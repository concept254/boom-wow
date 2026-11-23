<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    use SoftDeletes;

    public const TYPES = ['general' => 'General', 'store' => 'Store / business', 'service' => 'Service', 'product' => 'Product', 'announcement' => 'Announcement', 'promotion' => 'Promotion'];

    protected $fillable = [
        'business_unit_id', 'type', 'caption', 'heading', 'subheading', 'image_path', 'image_url',
        'cta_label', 'cta_url', 'cta2_label', 'cta2_url', 'sort_order', 'starts_on', 'ends_on', 'status',
    ];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    /** Published and inside its optional date window, in display order. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->where(fn ($w) => $w->whereNull('starts_on')->orWhere('starts_on', '<=', today()))
            ->where(fn ($w) => $w->whereNull('ends_on')->orWhere('ends_on', '>=', today()))
            ->orderBy('sort_order')->orderBy('id');
    }

    public function hasImage(): bool
    {
        return (bool) ($this->image_path || $this->image_url);
    }

    /** Uploaded image wins, then external URL, then a neutral placeholder so a slide is never blank. */
    public function getSrcAttribute(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        return $this->image_url ?: 'https://picsum.photos/seed/slide'.$this->id.'/1920/900';
    }
}
