<?php

use App\Http\Controllers\TicketCategorieController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/ticket_categories', [TicketCategorieController::class, 'store']);
    Route::get('/ticket_categories', [TicketCategorieController::class, 'index']);
    Route::get('/ticket_categories/{id}', [TicketCategorieController::class, 'show']);
    Route::put('/ticket_categories/{id}', [TicketCategorieController::class, 'update']);
});
