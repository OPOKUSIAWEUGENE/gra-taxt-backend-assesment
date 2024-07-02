<?php

use App\Http\Controllers\GRATaxController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(GRATaxController::class)->group(function () {
   
        Route::post('get-gross-salary', 'getGrossSalary');
        Route::post('get-net-income', 'getNetIncome');
});
