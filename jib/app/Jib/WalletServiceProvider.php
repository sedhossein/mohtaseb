<?php

namespace App\Jib;

use App\Jib\Data\Repositories\UserTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(WalletServiceInterface::class, function () {
            return new WalletService(new UserTransactionRepository(
                DB::table('user_transactions')
            ));
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
            WalletServiceInterface::class,
        ];
    }
}
