<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\BannerController;
use App\Http\Controllers\api\ColorSchemeController;
use App\Http\Controllers\api\TemplateController;
use App\Http\Controllers\api\BannerTypeController;
use App\Http\Controllers\api\BannerBlockController;
use App\Http\Controllers\api\AdminController;

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
    Route::get('/user', [ UserController::class, 'get' ]);
    Route::post('/user/store', [ UserController::class, 'store' ]);
    Route::post('/user/login', [ UserController::class, 'login' ]);
    Route::get('/user/login', [ UserController::class, 'login' ]);

    Route::get('/user/refresh', [ UserController::class, 'refresh' ]);

    Route::get('/banner', [ BannerController::class, 'getPublicBanner' ]);
    Route::get('/banners', [ BannerController::class, 'getAllPublicBanners' ]);
    Route::get('/banner/export', [ BannerController::class, 'export' ]);

    Route::middleware('auth:api')->group(function () {
        Route::get('/user/all', [ UserController::class, 'get' ]);
        Route::post('/user/logout', [ UserController::class, 'logout' ]);
        Route::delete('/user/delete', [ UserController::class, 'delete' ]);

        Route::get('/user/banners', [ BannerController::class, 'getAll' ]);
        Route::get('/user/banner', [ BannerController::class, 'get' ]);
        Route::post('/user/banners', [ BannerController::class, 'store' ]);
        Route::delete('/user/banner', [ BannerController::class, 'delete' ]);

        Route::get('/banner-types', [ BannerTypeController::class, 'get' ]);

        Route::get('/banner-blocks', [ BannerBlockController::class, 'get' ]);

        Route::get('/user/color-schemes', [ ColorSchemeController::class, 'getAll' ]);
        Route::get('/user/color-scheme', [ ColorSchemeController::class, 'get' ]);
        Route::post('/user/color-scheme', [ ColorSchemeController::class, 'store' ]);
        Route::delete('/user/color-scheme', [ ColorSchemeController::class, 'delete' ]);

        Route::get('/user/templates', [ TemplateController::class, 'getAll' ]);
        Route::get('/user/template', [ TemplateController::class, 'get' ]);
        Route::get('/user/template/info', [ TemplateController::class, 'getTemplateBlocks' ]);
        Route::post('/user/template', [ TemplateController::class, 'store' ]);
        Route::delete('/user/template', [ TemplateController::class, 'delete']);

        Route::middleware('admin')->group(function () {
            Route::get('/admin/users', [ AdminController::class, 'getUsers' ]);
            Route::get('/admin/user', [ AdminController::class, 'getUser' ]);
            Route::post('/admin/user', [ AdminController::class, 'changeUserRole' ]);
            Route::delete('/admin/user', [ AdminController::class, 'deleteUser' ]);

            Route::get('/admin/banners', [ AdminController::class, 'getBanners' ]);
            Route::delete('/admin/banner', [ AdminController::class, 'deleteBanner' ]);

            Route::get('/admin/user/roles', [ AdminController::class, 'getUserRoles' ]);
        });
    });
});
