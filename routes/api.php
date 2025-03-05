<?php
// resources
// 
// controllers 
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FavoriteController;

// others 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function (){
        Route::post('/login-by-email', [CustomerController::class, 'login']);
        Route::post('/login-by-number', [CustomerController::class, 'loginByNumber']);
        Route::post('/register-by-phone', [CustomerController::class, 'registerByPhone']);
        Route::post('/verify-code', [CustomerController::class, 'verifyCode']);
        Route::put('/forgot-password', [CustomerController::class, 'forgotPassword']);
        Route::put('/reset-password', [CustomerController::class, 'resetPassword']);
        Route::put('/register-name-email', [CustomerController::class, 'register']);
        Route::post('/logout', [CustomerController::class, 'logout'])->middleware('auth:sanctum');
    });
    Route::prefix('brand')->group(function (){
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{id}', [BrandController::class, 'show']);
    });
    Route::prefix('category')->group(function (){
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/main', [CategoryController::class, 'mainCategories']);
        Route::get('/{parentId}/sub', [CategoryController::class, 'subCategories']);
        Route::get('/{id}', [CategoryController::class, 'show']);
    });

    Route::prefix('order')->group(function (){
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']); 
        Route::post('/regular', [OrderController::class, 'createRegularOrder']); 
        Route::post('/custom', [OrderController::class, 'createCustomOrder']);
        Route::put('update/{id}', [OrderController::class, 'updateOrder']);
    });
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::get('/{id}', [FavoriteController::class, 'show']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{id}', [FavoriteController::class, 'destroy']);
        Route::delete('/all', [FavoriteController::class, 'destroyAll']);
    });

});