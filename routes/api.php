<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', action: function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::group(['middleware' => 'auth'], function () {
    // logout
    Route::post('/logout', 'App\Http\Controllers\AuthController@logout');
    // common
    Route::group(['prefix' => 'common'], function () {
        // loadMenu
        Route::get('/loadMenu', 'App\Http\Controllers\CommonController@loadMenu');
    });
});