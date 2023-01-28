<?php

use Illuminate\Support\Facades\Route;

Route::any('/tripay/callback', 'Hanoivip\PaymentMethodTripay\TripayController@callback')->name('tripay.callback');