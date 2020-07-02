<?php

namespace App\Karim;

use App\Karim\Data\Decorators\CellphoneDecorator;
use App\Karim\Data\Repositories\CacheKeyHelper;
use App\Karim\Data\Repositories\GiftCodeRepository;
use App\Karim\Data\Repositories\StatisticsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Redis;

class VoucherServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(VoucherServiceInterface::class, function () {
            $redis = new Redis;
            $redis->connect(
                config('database.redis.default.host'),
                config('database.redis.default.port')
            );

            $repository = new GiftCodeRepository(
                DB::table('gift_codes'),
                DB::table('winners'),
                new CacheKeyHelper,
                $redis,
                config('cache.redis.gift_code.duplicates_ttl', 0),                 // For ever
                config('cache.redis.gift_code.duplicates_lock_ttl', 2),            // 3 seconds
                config('cache.redis.gift_code.update_remaining_lock_ttl', 2)       // 3 seconds
            );

            $statistics = new StatisticsRepository(DB::table('winners'));

            return new VoucherService(
                $repository,
                $statistics,
                new CellphoneDecorator()
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            VoucherServiceInterface::class
        ];
    }
}
