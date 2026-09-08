<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketCategorieRequest;
use App\Http\Requests\TicketRequest;
use App\Models\TicketCategorie;

class TicketCategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $ticket_categories = TicketCategorie::all();

            return ResponseHandler::success($ticket_categories, 'Categorías de Tickets Obtenidos Correctamente', 200);
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
            $ticket_categories = TicketCategorie::create($request->valited());

            return ResponseHandler::success($ticket_categories, 'Categoría de Tickets Creado Correctamente',201);
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
            $ticket_categories = $this->findTicketCategoriedOrFail($id);

            return ResponseHandler::success($ticket_categories, 'Categoría de Tickets Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketCategorieRequest $request, string $id)
    {
        try {
            $ticket_categories = $this->findTicketCategoriedOrFail($id);

            $ticket_categories->update($request->validated());

            return ResponseHandler::success($ticket_categories, 'Categoría de Tickets Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el ticket no existe.
     */
    private function findTicketCategoriedOrFail(string $id): TicketCategorie
    {
        $ticket_categories = TicketCategorie::find($id);

        if (! $ticket_categories) {
            throw new NotFoundError('Ticket no encontrado');
        }

        return $ticket_categories;
    }

}
