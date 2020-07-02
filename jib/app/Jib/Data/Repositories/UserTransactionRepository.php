<?php

namespace App\Jib\Data\Repositories;

use App\Jib\Contracts\UserRepositoryContract;
use App\Jib\Data\Models\Wallet;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UserTransactionRepository implements UserRepositoryContract
{
    private $userTransactionsQueryBuilder;

    public function __construct(Builder $userTransactionsQueryBuilder)
    {
        $this->userTransactionsQueryBuilder = $userTransactionsQueryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getWallet(int $id): Wallet
    {
        $latestTransaction = (clone $this->userTransactionsQueryBuilder)
            ->select(['current_balance'])
            ->where('user_id', $id)
            ->latest('id')
            ->first();

        return (new Wallet)->withBalance(
            $latestTransaction->current_balance ?? 0
        );
    }

    /**
     * @inheritDoc
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description): string
    {
        try {
            DB::beginTransaction();
            $wallet = $this->getWallet($userID);
            $referenceID = $this->generateUniqueReferenceId();

            (clone $this->userTransactionsQueryBuilder)
                ->insert([
                    'user_id' => $userID,
                    'transaction_type' => $transactionType,
                    'transaction_reference' => $referenceID,
                    'creditor' => $amount,
                    'debtor' => 0,
                    'current_balance' => $amount + $wallet->getBalance(),
                    'description' => $description,
                ]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $referenceID;
    }

    private function generateUniqueReferenceId(): string
    {
        return md5(uniqid(rand(), true));
    }
}
