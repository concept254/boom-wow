<?php

namespace App\Models\Concerns;

use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Business-unit isolation for records that belong to a staff member rather than directly to a business.
 * StaffProfile itself is scoped by BelongsToBusiness, so whereHas('staff') inherits that restriction.
 */
trait ScopedThroughStaff
{
    public static function bootScopedThroughStaff(): void
    {
        static::addGlobalScope('business_via_staff', function (Builder $builder) {
            if (! app()->bound('admin_scope') || ! app('admin_scope')) {
                return;
            }
            $user = Auth::user();
            if (! $user || $user->hasRole('Super Admin')) {
                return;
            }
            $builder->whereHas('staff');
        });
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }
}
