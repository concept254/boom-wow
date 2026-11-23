<?php

namespace App\Models\Concerns;

use App\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Business-unit data isolation.
 *
 * Staff-side queries (admin area) are narrowed to the businesses the signed-in
 * user is assigned to, unless they are a Super Admin. Public/customer queries
 * run with no scope because the scope only applies when `admin_scope` is true
 * on the request, which the admin route group switches on.
 */
trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $builder) {
            if (! app()->bound('admin_scope') || ! app('admin_scope')) {
                return;
            }
            $user = Auth::user();
            if (! $user || $user->hasRole('Super Admin')) {
                return;
            }
            $builder->whereIn($builder->getModel()->getTable().'.business_unit_id', $user->businessIds());
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    public function scopeForBusiness(Builder $query, int $businessId): Builder
    {
        return $query->where($this->getTable().'.business_unit_id', $businessId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->getTable().'.status', 'published');
    }
}
