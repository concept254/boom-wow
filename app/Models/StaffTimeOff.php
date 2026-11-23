<?php

namespace App\Models;

use App\Models\Concerns\ScopedThroughStaff;
use Illuminate\Database\Eloquent\Model;

class StaffTimeOff extends Model
{
    use ScopedThroughStaff;

    protected $table = 'staff_time_off';

    protected $fillable = ['staff_profile_id', 'starts_at', 'ends_at', 'reason'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
