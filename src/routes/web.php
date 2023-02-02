<?php
use Illuminate\Support\Facades\Route;
use Nrbsolution\nagad_payment_gateway\Http\Controllers\NagadPaymentGatewayController;

// Route::group(['prefix'=>'api'], function () {
// });
    Route::get('/nagad/{reference_id}/{amount}', [NagadPaymentGatewayController::class, 'NagadPay']);
    Route::get('/nagad/callback', [NagadPaymentGatewayController::class, 'NagadCallback'])->name('nagad.callback');
    Route::get("/nagad-payment/{transaction_id}/success", [NagadPaymentGatewayController::class, "success"]);
    Route::get("/nagad-payment/{transaction_id}/fail", [NagadPaymentGatewayController::class, "fail"]);
