<?php

use App\Http\Controllers\GRATaxController;
use Illuminate\Support\Facades\Route;


Route::controller(GRATaxController::class)->group(function () {
    Route::prefix('gra-tax-service')->group(function () {
        Route::post('get-gross-salary', 'getGrossSalary');
        Route::post('get-net-income', 'getNetIncome');
    });
});
