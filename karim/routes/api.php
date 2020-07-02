<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // Gift Code
    Route::group(['prefix' => 'gifts'], function () {
        Route::post('/', 'GiftCodeController@apply');
        Route::get('/winners', 'GiftCodeController@getWinners');
    });
});
