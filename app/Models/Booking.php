<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'reschedule_requested', 'no_show'];

    protected $fillable = ['reference', 'business_unit_id', 'user_id', 'service_id', 'staff_profile_id', 'starts_at', 'ends_at', 'status', 'notes', 'reschedule_note', 'reminder_sent_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'reminder_sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}
