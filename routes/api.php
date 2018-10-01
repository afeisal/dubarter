<?php

use Illuminate\Http\Request;

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


Route::post('login','UserController@login')->name('login');
Route::post('user/{id}','UserController@show');

Route::middleware('auth:api', 'throttle:20,1')->group(function () {
    Route::post('users','UserController@index');
    Route::post('edit/users/{id}','UserController@update');
    Route::post('add/user','UserController@store');
});
