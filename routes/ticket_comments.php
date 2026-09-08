<?php

use App\Http\Controllers\TicketCommentController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/ticket_comments', [TicketCommentController::class, 'store']);
    Route::get('/ticket_comments', [TicketCommentController::class, 'index']);
    Route::get('/ticket_comments/{id}', [TicketCommentController::class, 'show']);
    Route::put('/ticket_comments/{id}', [TicketCommentController::class, 'update']);
});
