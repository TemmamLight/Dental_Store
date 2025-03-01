<?php
// resources
// 
// controllers 
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;

// others 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('customer')->group(function (){
        Route::post('/login', [CustomerController::class, 'login']);
    });
    Route::prefix('brand')->group(function (){
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{id}', [BrandController::class, 'show']);
    });
    Route::prefix('category')->group(function (){
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/main', [CustomerController::class, 'mainCategories']);
        Route::get('/{parentId}/sub', [CustomerController::class, 'subCategories']);
        Route::get('/{id}', [CategoryController::class, 'show']);
    });

    Route::prefix('order')->group(function (){
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']); 
        Route::post('/regular', [OrderController::class, 'createRegularOrder']); 
        Route::post('/custom', [OrderController::class, 'createCustomOrder']);
        Route::put('update/{id}', [OrderController::class, 'updateOrder']);
    });

});