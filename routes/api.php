<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\OrderController;

Route::get('/', function() {
    return Json(true);
});

//회원 가입
Route::post('register', [RegisterController::class, 'register']);
//로그인
Route::post('login', [RegisterController::class, 'login']);

//로그인 확인
Route::middleware('auth:api')->group( function() {
    Route::prefix('room')->group(function() {
        //목록 조회
        Route::get('/', [RoomController::class, 'list']);
        //목록 차트 조회
        Route::get('chart', [RoomController::class, 'chart']);
        //목록 생성
        Route::post('/', [RoomController::class, 'add']);
        //특정 목록 조회
        Route::get('{id}', [RoomController::class, 'room']);
        //특정 목록 수정
        //Route::put('{id}', [RoomController::class, 'edit']);
        //특정 목록 삭제
        Route::delete('{id}', [RoomController::class, 'delete']);
        //방 마감
        Route::put('{id}/{type}', [RoomController::class, 'state']);
    });

    Route::prefix('order')->group(function() {
        //메뉴 접수 목록 조회
        Route::get('{room_id}', [OrderController::class, 'list']);
        //메뉴 접수
        Route::post('{room_id}', [OrderController::class, 'add']);
        //메뉴 수정
        Route::put('{id}', [OrderController::class, 'edit']);
        //메뉴 삭제
        Route::delete('{id}', [OrderController::class, 'delete']);
    });
});                                                                                             
