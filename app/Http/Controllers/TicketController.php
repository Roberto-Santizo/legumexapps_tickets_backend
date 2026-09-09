<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketRequest;
use App\Services\MicrosoftGraphMailService;
use App\Enums\UserRole;
use App\Errors\UnauthorizedError;
use App\Models\Ticket;
use App\Models\User;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            if ($user->role === UserRole::ADMIN) {
                $tickets = Ticket::all();
            } else {
                $tickets = Ticket::where('user_id', $user->id->get());
            }

            return ResponseHandler::success($tickets, 'Tickets Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request, MicrosoftGraphMailService $mail)
    {
        try {
            $data = $request->validated();

            $data['user_id'] = auth()->id();

            $adminusers = User::where('role', '=', 'admin')->get()->first();
            $emails = ['soportetecnico.tejar@legumex.net', $adminusers->email];
            $ticket = Ticket::create($data);


            $mail->to($emails)->subject('Ticket Creado #' . $ticket->ticket_number)
                ->html(" <h2>Ticket creado correctamente</h2> <p>Hola {$ticket->user->name},</p> <p>Tu ticket ha sido creado correctamente.</p> <p> <strong>Número:</strong> {$ticket->ticket_number} </p> <p> <strong>Título:</strong> {$ticket->title} </p> <p> <strong>Descripción:</strong> {$ticket->description} </p> ")->send();

            return ResponseHandler::success($ticket, 'Ticket Creado Correctamente', 201);
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
            $user = auth()->user();

            if ($user->role !== UserRole::ADMIN && $ticket->user_id !== $user->id) {
                throw new UnauthorizedError('No Autorizado');
            }

            return ResponseHandler::success($ticket, 'Ticket Obtenido Correctamente', 200);
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
            $user = auth()->user();

             if ($user->role !== UserRole::ADMIN && $ticket->user_id !== $user->id) {
                throw new UnauthorizedError('No Autorizado');
            }

            $ticket->update($request->validated());

            return ResponseHandler::success($ticket, 'Ticket Actualizado Correctamente', 200);
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
