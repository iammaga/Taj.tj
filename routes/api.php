<?php

use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\MessageController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('ads')->group(function () {
    Route::get('/', [AdController::class, 'index']);
    Route::get('/{id}', [AdController::class, 'show']);
    Route::post('/', [AdController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{id}', [AdController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [AdController::class, 'destroy'])->middleware('auth:sanctum');

    Route::get('/category/{categoryId}', [AdController::class, 'adsByCategory']);
    Route::post('/{id}/favorite', [AdController::class, 'toggleFavorite'])->middleware('auth:sanctum');
    Route::get('/favorites', [AdController::class, 'getFavorites'])->middleware('auth:sanctum');
    Route::post('/{id}/review', [AdController::class, 'addReview'])->middleware('auth:sanctum');
    Route::get('/{id}/reviews', [AdController::class, 'getReviews']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'sendMessage']);
        Route::get('/', [MessageController::class, 'getMessages']);
        Route::get('/{userId}', [MessageController::class, 'getConversation']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/me', [UserController::class, 'showMe']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::put('/me/password', [UserController::class, 'updatePassword']);
    });
});
