<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketHistoryRequest;
use App\Http\Resources\TicketHistoryResource;
use App\Models\TicketHistory;

class TicketHistoryController extends Controller
{
    public function index()
    {
        try {
            $ticket_histories = TicketHistory::all();

            return ResponseHandler::success(TicketHistoryResource::collection($ticket_histories),'Historial de Tickets obtenidos correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(TicketHistoryRequest $request)
    {
        try {
            $data = $request->validated();

            $data['user_id'] = auth()->id();

            $ticket_histories = TicketHistory::create($data);

            return ResponseHandler::success($ticket_histories, 'Historial de Tickets Creado Correctamente',201);
            } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function show(string $id)
    {
        try {
            $ticket_histories = $this->findTicketHistoryOrFail($id);

            return ResponseHandler::success($ticket_histories, 'Historial de Tickets Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(TicketHistoryRequest $request, string $id)
    {
        try {
            $ticket_histories = $this->findTicketHistoryOrFail($id);

            $ticket_histories->update($request->validated());

            return ResponseHandler::success($ticket_histories, 'Historial de Tickets Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    private function findTicketHistoryOrFail(string $id): TicketHistory
    {
        $ticket_histories = TicketHistory::find($id);

        if (! $ticket_histories) {
            throw new NotFoundError('Historial de Tickets no encontrado');
        }

        return $ticket_histories;
    }
}