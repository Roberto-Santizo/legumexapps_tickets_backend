<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);

    Route::patch('/tickets/{id}/assign', [TicketController::class, 'assign']);
    Route::patch('/tickets/{ticket}/closed', [TicketController::class, 'closed']);
});
