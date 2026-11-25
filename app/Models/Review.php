<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A customer review of a Product or Service. New reviews wait for approval in Admin > Customer Reviews. */
class Review extends Model
{
    use BelongsToBusiness, SoftDeletes;

    protected $fillable = ['reviewable_type', 'reviewable_id', 'business_unit_id', 'user_id', 'name', 'rating', 'title', 'body', 'is_verified', 'status', 'reply'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_verified' => 'boolean'];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getItemNameAttribute(): string
    {
        return $this->reviewable?->name ?? '(removed item)';
    }
}
