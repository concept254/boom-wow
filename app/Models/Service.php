<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasReviews;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use BelongsToBusiness, HasFactory, HasReviews, SoftDeletes;

    protected $fillable = ['business_unit_id', 'service_category_id', 'name', 'slug', 'description', 'highlights', 'image_path', 'price', 'price_label', 'duration_minutes', 'is_bookable', 'requires_staff', 'is_featured', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_bookable' => 'boolean', 'requires_staff' => 'boolean', 'is_featured' => 'boolean', 'highlights' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $s) {
            if (blank($s->slug)) {
                $base = \Illuminate\Support\Str::slug((string) $s->name) ?: 'service';
                $slug = $base;
                for ($i = 2; static::withTrashed()->where('slug', $slug)->where('id', '!=', (int) $s->id)->exists(); $i++) {
                    $slug = $base.'-'.$i;
                }
                $s->slug = $slug;
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    /** Cover image first, then the gallery; a neutral local placeholder when nothing has been uploaded. */
    public function getGallerySlidesAttribute(): array
    {
        $slides = [];
        if ($this->image_path) {
            $slides[] = ['url' => Storage::disk('public')->url($this->image_path), 'alt' => $this->name];
        }
        foreach ($this->images as $i) {
            $slides[] = ['url' => Storage::disk('public')->url($i->path), 'alt' => $i->alt ?: $this->name, 'caption' => $i->alt];
        }

        return $slides ?: [['url' => asset('images/placeholder.svg'), 'alt' => $this->name, 'placeholder' => true]];
    }

    public function getImageUrlAttribute(): string
    {
        return $this->gallery_slides[0]['url'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(StaffProfile::class, 'service_staff');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
