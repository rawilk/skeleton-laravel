<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware(array_filter(['auth:web', \Rawilk\LaravelBase\Features::enabled(\Rawilk\LaravelBase\Features::emailVerification()) ? 'verified' : null]));
