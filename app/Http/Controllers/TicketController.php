<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketRequest;
use App\Models\Ticket;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $tickets = Ticket::all();

            return ResponseHandler::success($tickets,'Tickets Obtenidos Correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request)
    {
        try {
            $data = $request->validated();

            $data['user_id'] = auth()->id();

            $ticket = Ticket::create($data);

            return ResponseHandler::success($ticket,'Ticket Creado Correctamente',201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $ticket = $this->findTicketOrFail($id);

            return ResponseHandler::success($ticket,'Ticket Obtenido Correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketRequest $request, string $id)
    {
        try {
            $ticket = $this->findTicketOrFail($id);

            $ticket->update($request->validated());

            return ResponseHandler::success($ticket,'Ticket Actualizado Correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el ticket no existe.
     */
    private function findTicketOrFail(string $id): Ticket
    {
        $ticket = Ticket::find($id);

        if (! $ticket) {
            throw new NotFoundError('Ticket no encontrado');
        }

        return $ticket;
    }
}
