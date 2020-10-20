<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\BannerController;

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

Route::namespace('api')->group(function () {
    Route::get('/user', [ UserController::class, 'get' ] );
    Route::post('/user/store', [ UserController::class, 'store' ] );
    Route::post('/user/login', [ UserController::class, 'login' ] );
    Route::get('/user/login', [ UserController::class, 'login' ] );

    Route::get('/user/refresh', [ UserController::class, 'refresh' ] );

    Route::middleware('auth:api')->group(function () {
        Route::get('/user/all', [ UserController::class, 'userInfo' ] );
        Route::post('logout', [ UserController::class, 'logout' ] );
        Route::delete('/user/delete', [ UserController::class, 'delete' ]);

        Route::get('/user/banners', [ BannerController::class, 'getUserBanners' ] );
        Route::get('/user/banner', [ BannerController::class, 'getSpecificBanner' ] );
        Route::post('/user/banners', [ BannerController::class, 'saveBanner' ] );
    });
});
