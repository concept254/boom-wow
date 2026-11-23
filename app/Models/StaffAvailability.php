<?php

namespace App\Models;

use App\Models\Concerns\ScopedThroughStaff;
use Illuminate\Database\Eloquent\Model;

class StaffAvailability extends Model
{
    use ScopedThroughStaff;

    protected $fillable = ['staff_profile_id', 'day_of_week', 'starts_at', 'ends_at'];
}
