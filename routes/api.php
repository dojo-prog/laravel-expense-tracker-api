<?php

use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ExpenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //  V1
    Route::prefix('v1')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::apiResource('budgets', BudgetController::class);
    });
});

require __DIR__.'/auth.php';
