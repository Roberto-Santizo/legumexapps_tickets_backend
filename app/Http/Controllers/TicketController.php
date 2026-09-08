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
        try{
            $tickets = Ticket::all();

            return ResponseHandler::success($tickets, 'Tickets Obtenidos Correctamente', 200);
        } catch (\Throwable $th){
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request)
    {
        try {
            $tickets = Ticket::create($request->valited());

            return ResponseHandler::success($tickets, 'Ticket Creado Correctamente',201);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $tickets = $this->findTicketdOrFail($id);

            return ResponseHandler::success($tickets, 'Ticket Obtenido Correctamente', 200);
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
            $tickets = $this->findTicketdOrFail($id);

            $tickets->update($request->validated());

            return ResponseHandler::success($tickets, 'Ticket Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el ticket no existe.
     */
    private function findTicketdOrFail(string $id): Ticket
    {
        $tickets = Ticket::find($id);

        if (! $tickets) {
            throw new NotFoundError('Ticket no encontrado');
        }

        return $tickets;
    }

}
