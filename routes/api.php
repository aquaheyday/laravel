<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReceptionController;

Route::prefix('reception')->group(function () {
    Route::get('/', [ReceptionController::class, 'add']);
});
Route::get('/', [ReceptionController::class, 'add']);
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);

Route::middleware('auth:api')->group( function () {
    
});                                                                                             
