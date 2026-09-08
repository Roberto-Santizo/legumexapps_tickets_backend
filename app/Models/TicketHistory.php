<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id','user_id','action','old_value','new_value'])]
class TicketHistory extends Model
{
    //
}
