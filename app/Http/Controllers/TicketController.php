<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketRequest;
use App\Services\MicrosoftGraphMailService;
use App\Enums\UserRole;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Errors\UnauthorizedError;
use Symfony\Component\Mime\Email;
use App\Http\Requests\AssignTicketRequest;
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
                $tickets = Ticket::where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)->orWhere('assigned_to', $user->id);
                })->get();
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
            $data['status'] = TicketStatus::OPEN;
            $data['priority'] = TicketPriority::MEDIUM;

            $adminEmails = User::where('role', UserRole::ADMIN)->pluck('email')->toArray();
            $emails = $adminEmails;
            $ticket = Ticket::create($data);

            $html = view('emails.ticket', [
                'ticket' => $ticket,
            ])->render();

            $mail->to($emails)->subject('Ticket Creado')
                ->html($html)->embed(public_path('images/logo.jpeg'), 'logo-legumex', 'image/jpeg')->send();

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

    public function assign(AssignTicketRequest $request, string $id)
    {
        try {
            $ticket = $this->findTicketOrFail($id);

            $ticket->update(['assigned_to' => $request->validated('assigned_to')]);

            return ResponseHandler::success($ticket->load('assignedTo'), 'Ticket Asignado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function closed(Ticket $ticket, MicrosoftGraphMailService $mail)
    {
        try {

            $user = auth()->user();

            if ($user->role !== UserRole::ADMIN && $ticket->assigned_to !== $user->id) {
                return ResponseHandler::error(new \Exception('No tiene permiso para cerrar este ticket'));
            }

            $ticket->update(['status' => TicketStatus::CLOSED, 'closed_at' => now(), 'closed_by' => $user->id]);

            $ticket->load(['user', 'closedBy']);

            // Generamos la plantilla Blade
            $html = view('emails.ticket-closed', ['ticket' => $ticket,])->render();

            // Enviamos el correo al usuario que creó el ticket
            $mail->to($ticket->user->email)->subject('Ticket Cerrado')
            ->html($html)->embed(public_path('images/logo.jpeg'),'logo-legumex','image/jpeg')->send();

            return ResponseHandler::success($ticket->load('closedBy'), 'Ticket Cerrado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
}
