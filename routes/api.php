<?php

use App\Http\Controllers\Api\UserApiController;
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
    Route::get('users', 'Api\UserApiController@index');
    Route::post('addUser', 'Api\UserApiController@signup');
    Route::get('user/{id}', 'Api\UserApiController@show');
    Route::post('deleteUser/{id}', 'Api\UserApiController@destroy');
    Route::get('resendEmail/{id}', 'Api\UserApiController@resend');


    // Route::put('usuarios/{id}', 'Api\UserApiController@update');
    Route::delete('usuarios/apagar/{id}', 'Api\UserApiController@destroy');

    //Products
    Route::get('homeproduct', 'Api\HomeController@mostRequestedProduts');
    Route::get('groups', 'Api\HomeController@groups');
    Route::get('anuncios', 'Api\HomeController@anuncios');


    Route::get('email_verify', [UserApiController::class, 'verifyEmail']);

});
