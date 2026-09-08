<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketHistoryResource extends JsonResource
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
            'action'=>$this->action,
            'old_value'=>$this->old_value,
            'new_value'=>$this->new_value,
        ];
    }
}
