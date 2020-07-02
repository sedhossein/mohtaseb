<?php

use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    // Wallet
    Route::group(['prefix' => 'wallets'], function () {
        Route::get('/{user_id}', 'WalletController@getWallet');
        Route::post('/credits', 'WalletController@credits');
    });
});
