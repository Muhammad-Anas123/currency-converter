<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ConversionController;

Route::get('/', function () {
    return view('welcome');
});

