<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Resources\TicketCommentResource;
use App\Models\TicketComment;

class TicketCommentController extends Controller
{
    public function index()
    {
        try {
            $ticket_comments = TicketComment::all();

            return ResponseHandler::success(TicketCommentResource::collection($ticket_comments),'Comentarios de Tickets obtenidos correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(TicketCommentRequest $request)
    {
        try {
            $data = $request->validated();

            $data['user_id'] = auth()->id();

            $ticket_comments = TicketComment::create($data);

            return ResponseHandler::success($ticket_comments, 'Comentario de Tickets Creado Correctamente',201);
            } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function show(string $id)
    {
        try {
            $ticket_comments = $this->findTicketCommentOrFail($id);

            return ResponseHandler::success($ticket_comments, 'Comentarios de Tickets Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(TicketCommentRequest $request, string $id)
    {
        try {
            $ticket_comments = $this->findTicketCommentOrFail($id);

            $ticket_comments->update($request->validated());

            return ResponseHandler::success($ticket_comments, 'Comentarios de Tickets Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    private function findTicketCommentOrFail(string $id): TicketComment
    {
        $ticket_comments = TicketComment::find($id);

        if (! $ticket_comments) {
            throw new NotFoundError('Comentario de Tickets no encontrado');
        }

        return $ticket_comments;
    }
}