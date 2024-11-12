<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\RequestsController;
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
    // emailExists
    Route::get('emailExists', 'Api\UserApiController@emailExists');

    Route::get('user/{id}', 'Api\UserApiController@show');
    Route::post('deleteUser/{id}', 'Api\UserApiController@destroy');
    Route::post('editUser/{field}/{id}', 'Api\UserApiController@editUser');

    Route::get('resendEmail/{id}', 'Api\UserApiController@resend');


    // Route::put('usuarios/{id}', 'Api\UserApiController@update');
    Route::delete('usuarios/apagar/{id}', 'Api\UserApiController@destroy');

    //Products
    Route::get('homeproduct', 'Api\HomeController@mostRequestedProduts');
    Route::get('groups', 'Api\HomeController@groups');
    Route::get('anuncios', 'Api\HomeController@anuncios');


    Route::get('email_verify', [UserApiController::class, 'verifyEmail']);
    Route::get('email_verify2', [UserApiController::class, 'verifyEmail2']);
    Route::get('email_verify3', [UserApiController::class, 'verifyEmail3']);



    Route::get('paymentMethods', [RequestsController::class, 'paymentMethods']);

    Route::post('addRequest', [RequestsController::class, 'addRequest']);
    Route::get('myRequests/{id}', [RequestsController::class, 'myRequests']);
    Route::get('availableRequests', [RequestsController::class, 'availableRequests']);

    Route::post('editRequest/{id}/{field}', [RequestsController::class, 'editRequest']);

    Route::get('acceptedRequests/{id}', [RequestsController::class, 'acceptedRequests']);

    Route::get('request/{id}', [RequestsController::class, 'request']);
    Route::post('testNotification/{id}', [RequestsController::class, 'testNotification']);
    Route::post('updateExpoPushToken', [UserApiController::class, 'updateExpoPushToken']);

    Route::get('products', [HomeController::class, 'products']);
    Route::post('addProductReview', [RequestsController::class, 'addProductReview']);
    Route::get('myReviews/{id}', [RequestsController::class, 'myReviews']);
    Route::post('editMyReview/{id}', [RequestsController::class, 'editMyReview']);
    Route::get('product/{id}', [HomeController::class, 'product']);
    Route::post('addFavorite', [UserApiController::class, 'addFavorite']);
    Route::post('deleteFavorite/{id}', [UserApiController::class, 'deleteFavorite']);
    Route::get('favorites', [UserApiController::class, 'favorites']);
    Route::get('username', [UserApiController::class, 'username']);
    Route::post('uploadPic/{id}', [UserApiController::class, 'uploadPic']);
    Route::get('stats/{id}', [RequestsController::class, 'stats']);
    Route::get('myProducts/{id}', [RequestsController::class, 'myProducts']);


});
