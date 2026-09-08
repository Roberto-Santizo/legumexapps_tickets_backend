<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id','user_id','action','old_value','new_value'])]
class TicketHistory extends Model
{
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
