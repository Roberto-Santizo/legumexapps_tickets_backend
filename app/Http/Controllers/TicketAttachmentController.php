<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\TicketAttachmentRequest;
use App\Http\Resources\TicketAttachmentResource;
use App\Interfaces\Storage\ImageStorageServiceInterface;
use App\Models\TicketAttachment;

class TicketAttachmentController extends Controller
{
    public function index()
    {
        try {
            $ticket_attachments = TicketAttachment::all();

            return ResponseHandler::success(TicketAttachmentResource::collection($ticket_attachments),'Archivos Adjuntos obtenidos correctamente',200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(TicketAttachmentRequest $request, ImageStorageServiceInterface $imageStorage)
    {
        try {
            $data = $request->validated();
            $file = $request->file('file');
            $data['user_id'] = auth()->id();

            $data['file_name'] = $file->getClientOriginalName();;
            $data['mime_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
            $data['file_path'] = $imageStorage->store($request->file('file'));

            $ticket_attachments = TicketAttachment::create($data);

            return ResponseHandler::success($ticket_attachments, 'Archivos Adjuntos Creado Correctamente',201);
            } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function show(string $id)
    {
        try {
            $ticket_attachments = $this->findTicketAttchmentOrFail($id);

            return ResponseHandler::success($ticket_attachments, 'Historial de Tickets Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(TicketAttachmentRequest $request, string $id)
    {
        try {
            $ticket_attachments = $this->findTicketAttchmentOrFail($id);

            $ticket_attachments->update($request->validated());

            return ResponseHandler::success($ticket_attachments, 'Archivos Adjuntos Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    private function findTicketAttchmentOrFail(string $id): TicketAttachment
    {
        $ticket_attachments = TicketAttachment::find($id);

        if (! $ticket_attachments) {
            throw new NotFoundError('Archivos Adjuntos no encontrado');
        }

        return $ticket_attachments;
    }
}