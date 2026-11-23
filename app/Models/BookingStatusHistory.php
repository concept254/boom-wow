<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    protected $fillable = ['booking_id', 'from_status', 'to_status', 'changed_by', 'note'];
}
