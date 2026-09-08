<?php

use App\Http\Controllers\TicketCategorieController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/brands', [TicketCategorieController::class, 'store']);
    Route::get('/brands', [TicketCategorieController::class, 'index']);
    Route::get('/brands/{id}', [TicketCategorieController::class, 'show']);
    Route::put('/brands/{id}', [TicketCategorieController::class, 'update']);
});
