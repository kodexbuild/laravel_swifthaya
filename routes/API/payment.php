<?php

use App\Http\Controllers\API\V1\PaymentController;
use Illuminate\Support\Facades\Route;



Route::prefix("payments")->group(function () {

  Route::post('/initialize-payment', [PaymentController::class, 'init'])->name('payment.init')->middleware("auth:sanctum");

  Route::post('/confirm', [PaymentController::class, 'confirmPayment']);

  Route::post('/webhook', [PaymentController::class, 'handleWebhook']);

  Route::post('/list_banks', [PaymentController::class, 'listBanks']);

  Route::post('/transfer', [PaymentController::class, 'initiateTransfer'])->middleware("auth:sanctum");
  Route::post('/account', [PaymentController::class, 'verifyAccountNumber']);
  Route::post('/recipient/{userId}', [PaymentController::class, 'createTransferRecipient']);
});
