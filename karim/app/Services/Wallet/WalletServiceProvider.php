<?php

namespace App\Services\Wallet;

use App\Services\Wallet\Exceptions\InvalidWalletServiceException;
use App\Services\Wallet\Jib\JibService;
use GuzzleHttp\Client;
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
            switch (config('services.wallet.provider')) {
                case 'jib':
                    return new JibService(
                        new Client([
                            'base_uri' => config('services.wallet.providers.jib.base_url'),
                            'timeout' => floatval(config('services.wallet.providers.jib.timeout'))
                        ])
                    );
                default:
                    throw new InvalidWalletServiceException;
            }
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
