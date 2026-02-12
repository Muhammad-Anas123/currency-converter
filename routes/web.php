<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('converter');
})->name('home');

Route::get('/history', function () {
    return view('history');
})->name('history');