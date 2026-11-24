<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/** A brand we stock / partner with, shown in the logo carousel. */
class Brand extends Model
{
    use SoftDeletes;

    protected $attributes = ['show_on_home' => true, 'status' => 'published'];

    protected $fillable = ['business_unit_id', 'name', 'logo_path', 'website_url', 'show_on_home', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['show_on_home' => 'boolean'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->orderBy('sort_order')->orderBy('name');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
