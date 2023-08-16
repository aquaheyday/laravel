<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReceptionController;
use App\Http\Controllers\Api\OrderController;

Route::get('/', function() {
    echo "test";
});

//회원 가입
Route::post('register', [RegisterController::class, 'register']);
//로그인
Route::post('login', [RegisterController::class, 'login']);

Route::middleware('auth:api')->group( function() {
    Route::prefix('reception')->group(function() {
        //목록 생성
        Route::get('/', [ReceptionController::class, 'get']);
        //목록 생성
        Route::post('/', [ReceptionController::class, 'add']);
        //특정 목록 조회
        Route::get('{id}', [ReceptionController::class, 'list']);
        //특정 목록 수정
        Route::put('{id}', [ReceptionController::class, 'edit']);
        //특정 목록 삭제
        Route::delete('{id}', [ReceptionController::class, 'delete']);
        //목록 조회
        Route::get('lists', [ReceptionController::class, 'lists']);
    });

    Route::prefix('order')->group(function() {
        //메뉴 접수
        Route::get('{receptions_id}', [OrderController::class, 'get']);
        //메뉴 접수
        Route::post('{receptions_id}', [OrderController::class, 'add']);
        //메뉴 수정
        Route::put('{id}', [OrderController::class, 'edit']);
        //메뉴 삭제
        Route::delete('{id}', [OrderController::class, 'delete']);
    });
});                                                                                             
