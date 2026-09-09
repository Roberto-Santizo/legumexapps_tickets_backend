<?php

use App\Http\Controllers\TicketAttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/ticket_attachments', [TicketAttachmentController::class, 'store']);
    Route::get('/ticket_attachments', [TicketAttachmentController::class, 'index']);
    Route::get('/ticket_attachments/{id}', [TicketAttachmentController::class, 'show']);
    Route::put('/ticket_attachments/{id}', [TicketAttachmentController::class, 'update']);
});
    