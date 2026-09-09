<?php

use App\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/ticket_histories', [TicketHistoryController::class, 'store']);
    Route::get('/ticket_histories', [TicketHistoryController::class, 'index']);
    Route::get('/ticket_histories/{id}', [TicketHistoryController::class, 'show']);
    Route::put('/ticket_histories/{id}', [TicketHistoryController::class, 'update']);
});
    