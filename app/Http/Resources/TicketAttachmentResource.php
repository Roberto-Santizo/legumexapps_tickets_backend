<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'ticket_id'=>$this->ticket_id,
            'user_id'=>$this->user_id,
            'file_name'=>$this->file_name,
            'file_path'=>$this->file_path,
            'file_size'=>$this->file_size,
        ];
    }
}
