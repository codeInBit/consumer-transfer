<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'API'], function () {
    Route::group([
        'namespace' => 'Authentication',
        'prefix' => 'auth',
    ], function () {
        Route::post('register', 'AuthenticationController@register');
        Route::post('login', 'AuthenticationController@login');
    });

    Route::group([
        'prefix' => 'transfers',
        'middleware' => 'auth:api',
    ], function () {
        Route::post('/', 'TransferController@transfer');
        Route::get('/', 'TransferController@getTransfers');
    });
});
