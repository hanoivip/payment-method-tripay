<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth:web'
])->namespace('Hanoivip\PaymentMethodTripay')
->group(function () {
    Route::get('/tripay/channels', 'TripayController@listChannel')->name('tripay.channels');
    Route::post('/tripay/channels/select', 'TripayController@selectChannel')->name('tripay.channels.select');
    Route::get('/tripay/return', 'TripayController@return')->name('tripay.return');
});

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\PaymentMethodTripay')
->prefix('ecmin')
->group(function () {
    // Module index
    Route::get('/tripay', 'Admin@index')->name('ecmin.tsr');
    
});