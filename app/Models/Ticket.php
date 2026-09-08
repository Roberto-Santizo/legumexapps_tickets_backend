<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_number','title','description','status', 'priority','category_id','user_id','assigned_to'])]
class Ticket extends Model
{
    //
}
