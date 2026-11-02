<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

   
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{id}/balance', [AccountController::class, 'balance']);

    Route::post('/accounts/{id}/deposit', [DepositController::class, 'deposit']);

    Route::post('/accounts/{id}/withdraw', [WithdrawalController::class, 'withdraw']);

    Route::get('/accounts/{accountId}/transactions', [TransactionController::class, 'index']);

    Route::post('/transfers', [TransferController::class, 'transfer']);

});
