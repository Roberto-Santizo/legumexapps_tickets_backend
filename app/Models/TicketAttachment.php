<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id','user_id','fila_name','fila_path','mime_type','fila_size'])]
class TicketAttachment extends Model
{
    //
}
