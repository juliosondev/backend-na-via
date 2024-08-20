<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;





Route::group([
    'prefix' => 'v1',
    // 'namespace' => 'Api\v1',
    'middleware' => 'api',
], function () {
    // AUTH
    Route::post('login', 'Api\Auth\AuthApiController@login')->name('login');
    Route::post('logout', 'Api\Auth\AuthApiController@logout')->name('logout');
    Route::post('refresh', 'Api\Auth\AuthApiController@refresh');
    Route::post('me', 'Api\Auth\AuthApiController@me');

    // USUÁRIOS
    Route::get('usuarios', 'Api\UserApiController@index');
    Route::post('usuarios', 'Api\UserApiController@store');
    Route::get('usuarios/{id}', 'Api\UserApiController@show');
    Route::put('usuarios/{id}', 'Api\UserApiController@update');
    Route::delete('usuarios/apagar/{id}', 'Api\UserApiController@destroy');

    //Products
    Route::get('homeproduct', 'Api\HomeController@mostRequestedProduts');
});


