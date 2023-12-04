<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function() {
    return Json(true);
});

//회원 가입
Route::post('register', [RegisterController::class, 'register']);
//이메일 찾기
Route::post('eamil', [RegisterController::class, 'email']);
//비밀번호 찾기
Route::post('password', [RegisterController::class, 'password']);
//로그인
Route::post('login', [RegisterController::class, 'login']);

//로그인 확인
Route::middleware('auth:api')->group( function() {
    Route::prefix('room')->group(function() {
        //목록 조회
        Route::get('/', [RoomController::class, 'list']);
        //목록 차트 조회
        Route::get('chart', [RoomController::class, 'chart']);
        //목록 TOP 10 조희
        Route::get('top', [RoomController::class, 'top']);
        //목록 생성
        Route::post('/', [RoomController::class, 'add']);
        //특정 목록 조회
        Route::get('{token}', [RoomController::class, 'room']);
        //특정 목록 수정
        //Route::put('{id}', [RoomController::class, 'edit']);
        //특정 목록 삭제
        Route::delete('{token}', [RoomController::class, 'delete']);
        //방 마감
        Route::put('{token}/{type}', [RoomController::class, 'state']);
    });

    Route::prefix('order')->group(function() {
        //메뉴 접수 목록 조회
        Route::get('{token}', [OrderController::class, 'list']);
        //메뉴 접수
        Route::post('{token}', [OrderController::class, 'add']);
        //메뉴 수정
        Route::put('{id}', [OrderController::class, 'edit']);
        //메뉴 삭제
        Route::delete('{id}', [OrderController::class, 'delete']);
    });

    Route::prefix('user')->group(function() {
        //사용자 정보 조회
        Route::get('/', [UserController::class, 'info']);
        //사용자 정보 업데이트
        Route::put('{type}', [UserController::class, 'update']);
    });
});


