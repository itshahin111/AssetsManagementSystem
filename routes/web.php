<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::view('/explorer', 'app');
Route::view('/login', 'app');
