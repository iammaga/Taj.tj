<?php

use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\AuthController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/ads', [AdController::class, 'index']);
Route::get('/ads/{id}', [AdController::class, 'show']);
Route::post('/ads', [AdController::class, 'store'])->middleware('auth:sanctum');
Route::put('/ads/{id}', [AdController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/ads/{id}', [AdController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/ads/category/{categoryId}', [AdController::class, 'adsByCategory']);
Route::put('/ads/{id}', [AdController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/ads/{id}', [AdController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/ads/{id}/favorite', [AdController::class, 'toggleFavorite'])->middleware('auth:sanctum');
Route::get('/favorites', [AdController::class, 'getFavorites'])->middleware('auth:sanctum');
Route::post('/ads/{id}/review', [AdController::class, 'addReview'])->middleware('auth:sanctum');
Route::get('/ads/{id}/reviews', [AdController::class, 'getReviews']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/messages', [MessageController::class, 'getMessages']);
    Route::get('/messages/{userId}', [MessageController::class, 'getConversation']);
});
