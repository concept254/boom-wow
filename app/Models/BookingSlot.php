<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSlot extends Model
{
    protected $fillable = ['booking_id', 'resource_key', 'slot_start'];

    protected function casts(): array
    {
        return ['slot_start' => 'datetime'];
    }
}
