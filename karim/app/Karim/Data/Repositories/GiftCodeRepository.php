<?php

namespace App\Karim\Data\Repositories;

use App\Karim\Contracts\GiftCodeRepositoryContract;
use App\Karim\Exceptions\ApplyGiftCodeFailureException;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\InvalidGiftCodeException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Redis;
use Throwable;

class GiftCodeRepository implements GiftCodeRepositoryContract
{
    private $giftCodesTableQueryBuilder;

    private $winnersTableQueryBuilder;

    private $cachedGiftCodeModel;

    private $cache;

    private $cacheKeyHelper;

    private $duplicatesTTL;

    private $duplicatesLockTTL;

    private $updateRemainingLockTTL;

    public function __construct(
        Builder $giftCodesTableQueryBuilder,
        Builder $winnersTableQueryBuilder,
        CacheKeyHelper $cacheKeyHelper,
        Redis $redis,
        int $duplicatesTTL,
        int $duplicatesLockTTL,
        int $updateRemainingLockTTL)
    {
        $this->giftCodesTableQueryBuilder = $giftCodesTableQueryBuilder;
        $this->winnersTableQueryBuilder = $winnersTableQueryBuilder;
        $this->cachedGiftCodeModel = null;
        $this->cacheKeyHelper = $cacheKeyHelper;
        $this->cache = $redis;
        $this->duplicatesTTL = $duplicatesTTL;
        $this->duplicatesLockTTL = $duplicatesLockTTL;
        $this->updateRemainingLockTTL = $updateRemainingLockTTL;
    }

    /**
     * @inheritDoc
     */
    public function isDuplicateRequest(int $id, string $code): bool
    {
        $lockKey = $this->cacheKeyHelper->duplicatesLock($id, $code);
        $duplicatesKey = $this->cacheKeyHelper->duplicates($code);

        // Check is this user request locked? or is it exist in duplicate list or not.
        if ($this->cache->get($lockKey) || $this->cache->sIsMember($duplicatesKey, $id)) {
            return true;
        }

        // Current request is not duplicate, but other requests with this key(id:code)
        // from current user could be locked to avoid concurrency issues and
        // using redis atomic lock to drop too many request in the moment
        $this->cache->set($lockKey, 1, $this->duplicatesLockTTL);

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isGiftCodeRemaining(string $code): bool
    {
        $remains = $this->cache->get(
            $this->cacheKeyHelper->giftCodeRemaining($code
            ));

        if ($remains === false) {
            // so gift_code key is not exist in cache
            $remains = $this->updateGiftCodeRemaining($code);
        }

        return intval($remains) > 0;
    }

    /**
     * @inheritDoc
     */
    public function applyGiftCode(int $id, string $code)
    {
        $duplicatesCacheKey = $this->cacheKeyHelper->duplicates($code);

        try {
            // to avoid concurrency
            DB::transaction(function () use ($id, $code, $duplicatesCacheKey) {
                $giftCode = $this->getGiftCodeModel($code);

                (clone $this->winnersTableQueryBuilder)
                    ->insert([
                        'user_id' => $id,
                        'gift_id' => $giftCode['id'],
                    ]);

                // update giftCodeRemaining cache value
                $this->cache->decr(
                    $this->cacheKeyHelper->giftCodeRemaining($giftCode['code'])
                );

                // This transaction will fail if "remaining" is going to be less than zero
                // because the "remaining" column is an unsigned one.
                (clone $this->giftCodesTableQueryBuilder)
                    ->where('code', $giftCode['code'])
                    ->decrement('remaining', 1);
            });

            // update duplicates list on cache
            $this->cache->sAdd($duplicatesCacheKey, $id);
        } catch (Throwable $exception) {// It's actually a QueryException but this works too
            Log::error($exception);
            if ($exception->getCode() == 23505) {
                // Deal with duplicate key error
                $this->cache->sAdd($duplicatesCacheKey, $id);
                throw new DuplicateGiftCodeRequestException;
            }

            throw new ApplyGiftCodeFailureException;
        }
    }

    /**
     * @inheritDoc
     */
    public function getGiftCode(string $code): array
    {
        // we don't want to catch this exception, to it can be raised to upper layers.
        return $this->getGiftCodeModel($code);
    }

    /**
     * @inheritDoc
     */
    public function revertGiftCode(int $id, string $code): void
    {
        $giftCode = $this->getGiftCodeModel($code);

        // to avoid concurrency
        DB::transaction(function () use ($id, $giftCode) {
            (clone $this->giftCodesTableQueryBuilder)
                ->where('code', $giftCode['code'])
                ->whereNull('expired_at')
                ->increment('remaining', 1);

            // update giftCodeRemaining cache value
            $this->cache->incr($this->cacheKeyHelper->giftCodeRemaining($giftCode['code']));

            (clone $this->winnersTableQueryBuilder)
                ->where([
                    'user_id' => $id,
                    'gift_id' => $giftCode['id'],
                ])
                ->delete();
        });

        $cacheKey = $this->cacheKeyHelper->duplicates($code);
        $this->cache->sRem($cacheKey, $id);
    }


    // ====================== LOCAL FUNCTIONALITIES ======================

    /**
     * This method calls when we have an incident, like Redis downtime.
     * So its not a common function that executes in all of the requests.
     * It just executes when the `giftCodeRemaining` value is missed on our cache.
     *
     * We need to handle concurrency issues on this process and prevent passing
     * all of the requests into database requests or cache hits, So I use CACHE LOCK.
     * On a worst-case, `updateGiftCodeRemaining()` at most will wait 2 seconds and after this,
     * going to updating the cache value by itself with a request to the database.
     *
     * In other way, maybe in this waiting time, other processes update the `giftCodeRemaining` value
     * in cache, and in this situation, we stop waiting and getting updated value from the cache immediately.
     *
     * @throws InvalidGiftCodeException
     */
    protected function updateGiftCodeRemaining(string $code): int
    {
        $start = time();
        $isLock = false;
        while ($isLock = $this->isGiftCodeCounterLocked() && (time() - $start) < 3) {
            // sleep needed to control too much redis hits in waiting loop.
            usleep(250);
        }

        if ($isLock) {
            // gift code remains count is set by another process just now,
            // so we can use redis for getting update value.
            return $this->cache->get($this->cacheKeyHelper->GiftCodeRemaining($code));
        }

        $cacheLockKey = $this->cacheKeyHelper->giftCodeRemainingLock();

        // NOTICE: cacheLock TTL value could be having short time, because if our process execute time being too much,
        // cache lock key will be expire and removing automatically from redis.
        // and so this can help other processes begin their process again.
        $this->cache->set($cacheLockKey, true);      // #### locking cache

        $giftCode = $this->getGiftCodeModel($code);
        $remains = $giftCode['remaining'];

        $this->cache->set($this->cacheKeyHelper->giftCodeRemaining($code), $remains);
        $this->cache->set($cacheLockKey, false);    // #### unlocking cache

        return $remains;
    }

    protected function isGiftCodeCounterLocked(): bool
    {
        $cacheKey = $this->cacheKeyHelper->giftCodeRemainingLock();

        // if $cacheKey value is true, one process in going to update cache,
        // so we return `true` to notice others to wait for respond.
        // and if $cacheKey is false, key is not exist in cache(or expired),
        // so we decide to returning `false`, to notice we are in unlock status and cache need update.
        return $this->cache->get($cacheKey);
    }

    /**
     * @throws InvalidGiftCodeException
     */
    protected function getGiftCodeModel(string $code): array
    {
        if ($this->cachedGiftCodeModel === null) {
            $giftCode = (clone $this->giftCodesTableQueryBuilder)
                ->select(['id', 'code', 'remaining', 'value', 'description'])
                ->where('code', $code)
                ->whereNull('expired_at')
                ->latest('id')
                ->first();

            if (empty($giftCode)) {
                throw new InvalidGiftCodeException("gift-code is not exists:$code");
            }

            $this->cachedGiftCodeModel = collect($giftCode)->toArray();
        }

        return $this->cachedGiftCodeModel;
    }
}
