<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;





Route::group([
    'prefix' => 'v1',
    // 'namespace' => 'Api\v1',
    // 'middleware' => 'web',
], function () {
    // AUTH
    Route::post('login', 'Api\Auth\AuthApiController@login');
    Route::post('logout', 'Api\Auth\AuthApiController@logout');
    Route::post('refresh', 'Api\Auth\AuthApiController@refresh');
    Route::post('me', 'Api\Auth\AuthApiController@me');
    Route::middleware('api')->get('login/google', 'Api\Auth\SocialateController@google');

    // USUÁRIOS
    Route::get('usuarios', 'Api\UserApiController@index');
    Route::post('usuarios', 'Api\UserApiController@store');
    Route::get('usuarios/{id}', 'Api\UserApiController@show');
    Route::put('usuarios/{id}', 'Api\UserApiController@update');
    Route::delete('usuarios/apagar/{id}', 'Api\UserApiController@destroy');

    // ACL
    Route::group(['prefix' => 'acl'], function(){
        Route::get('roles', 'Api\ACLController@roles');
        Route::get('role/{id}', 'Api\ACLController@role');
        Route::post('role/store', 'Api\ACLController@storeRole');
        Route::post('role/associarPermissoes/{id}', 'Api\ACLController@associarPermissoesStore');
        Route::get('role/permissoesAssociadas/{id}', 'Api\ACLController@permissoesAssociadas');
        Route::get('usuario/papeisAssociados/{id}', 'Api\ACLController@papeisAssociados');
        Route::post('usuario/associarPapeis/{id}', 'Api\ACLController@associarPapeisStore');
        Route::get('permissions', 'Api\ACLController@permissions');
        Route::post('permission/store', 'Api\ACLController@storePermission');
    });
});


