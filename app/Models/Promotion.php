<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = ['business_unit_id', 'title', 'description', 'image_path', 'link_url', 'starts_on', 'ends_on', 'status', 'placement', 'badge', 'cta_label', 'theme', 'sort_order'];

    public const PLACEMENTS = [
        'top_bar' => 'Top announcement bar (title + link only)',
        'home_specials' => 'Home page - specials cards',
        'home_banner' => 'Home page - wide advert banner',
        'inner_banner' => 'Inner pages - advert banner',
        'business_page' => "A business's own page - offer cards (choose the business above)",
    ];

    /** Active promotions for a placement (published + inside the date window), in display order. */
    public function scopeForPlacement($q, string $placement)
    {
        return $q->where('status', 'published')->where('placement', $placement)
            ->where(fn ($w) => $w->whereNull('starts_on')->orWhere('starts_on', '<=', today()))
            ->where(fn ($w) => $w->whereNull('ends_on')->orWhere('ends_on', '>=', today()))
            ->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeForBusiness($q, ?int $businessId)
    {
        return $businessId ? $q->where('business_unit_id', $businessId) : $q;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : null;
    }

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }
}
