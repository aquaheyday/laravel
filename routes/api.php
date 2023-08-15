<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReceptionController;

Route::get('/', function () {
    echo "test";
});

//회원 가입
Route::post('register', [RegisterController::class, 'register']);
//로그인
Route::post('login', [RegisterController::class, 'login']);

Route::middleware('auth:api')->group( function () {
    Route::prefix('reception')->group(function () {
        //목록 조회
        Route::get('/', [ReceptionController::class, 'lists']);
        //목록 생성
        Route::post('/', [ReceptionController::class, 'add']);
        //특정 목록 조회
        Route::get('{id}', [ReceptionController::class, 'list']);
        //특정 목록 수정
        Route::put('{id}', [ReceptionController::class, 'edit']);
        //특정 목록 삭제
        Route::delete('{id}', [ReceptionController::class, 'delete']);
    });
});                                                                                             
