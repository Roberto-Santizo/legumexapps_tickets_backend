<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id','user_id','file_name','file_path','mime_type','file_size'])]
class TicketAttachment extends Model
{
    //
}
