<?php

use Illuminate\Support\Facades\Route;

Route::post('/register', [\App\Http\Controllers\UserController::class, 'create']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('books')->group(function () {
    Route::get('/search', [\App\Http\Controllers\BookController::class, 'find']);
    Route::get('/', [\App\Http\Controllers\BookController::class, 'index']);
    Route::get('/{isbn}', [\App\Http\Controllers\BookController::class, 'get']);

    Route::middleware(['auth:sanctum', 'ability:update'])->group(function () {
        Route::post('/', [\App\Http\Controllers\BookController::class, 'create']);
        Route::patch('/{isbn}', [\App\Http\Controllers\BookController::class, 'update']);
        Route::delete('/{isbn}', [\App\Http\Controllers\BookController::class, 'delete']);
    });
});

Route::prefix('genres')->group(function () {
    Route::get('/', [\App\Http\Controllers\GenreController::class, 'index']);
    Route::get('/{genre}', [\App\Http\Controllers\GenreController::class, 'get']);

    Route::middleware(['auth:sanctum', 'ability:update'])->group(function () {
        Route::post('/', [\App\Http\Controllers\GenreController::class, 'create']);
        Route::patch('/{genre}', [\App\Http\Controllers\GenreController::class, 'update']);
        Route::delete('/{genre}', [\App\Http\Controllers\GenreController::class, 'delete']);
    });
});

Route::prefix('authors')->group(function () {
    Route::get('/', [\App\Http\Controllers\AuthorController::class, 'index']);
    Route::get('/{author}', [\App\Http\Controllers\AuthorController::class, 'get']);

    Route::middleware(['auth:sanctum', 'ability:update'])->group(function () {
        Route::post('/', [\App\Http\Controllers\AuthorController::class, 'create']);
        Route::patch('/{author}', [\App\Http\Controllers\AuthorController::class, 'update']);
        Route::delete('/{author}', [\App\Http\Controllers\AuthorController::class, 'delete']);
    });
});

Route::prefix('users')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/{user}', [\App\Http\Controllers\UserController::class, 'get']);
        Route::patch('/{user}', [\App\Http\Controllers\UserController::class, 'update']);
        Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'delete']);
    });

    Route::middleware(['auth:sanctum', 'ability:update'])->get('/', [\App\Http\Controllers\UserController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('favourites')->group(function () {
   Route::get('/', [\App\Http\Controllers\FavouriteController::class, 'index']);
   Route::post('/{isbn}', [\App\Http\Controllers\FavouriteController::class, 'create']);
   Route::delete('/{isbn}', [\App\Http\Controllers\FavouriteController::class, 'delete']);
});

Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('/', [\App\Http\Controllers\CartController::class, 'index']);
    Route::post('/{isbn}', [\App\Http\Controllers\CartController::class, 'create']);
    Route::delete('/{isbn}', [\App\Http\Controllers\CartController::class, 'delete']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/purchases', [\App\Http\Controllers\PurchaseController::class, 'index']);
    Route::post('/purchase/{bonus?}', [\App\Http\Controllers\PurchaseController::class, 'buy']);
});
