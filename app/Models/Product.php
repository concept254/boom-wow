<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasReviews;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToBusiness, HasFactory, HasReviews, SoftDeletes;

    protected static function booted(): void
    {
        // Slug is optional in the admin: build a unique-per-business slug from the name.
        static::saving(function (self $p) {
            if (blank($p->slug)) {
                $base = \Illuminate\Support\Str::slug((string) $p->name) ?: 'product';
                $slug = $base;
                for ($i = 2; static::withTrashed()->where('business_unit_id', $p->business_unit_id)->where('slug', $slug)->where('id', '!=', (int) $p->id)->exists(); $i++) {
                    $slug = $base.'-'.$i;
                }
                $p->slug = $slug;
            }
        });
    }

    public function getCurrentPriceAttribute(): string
    {
        $promo = $this->promo_price !== null && (! $this->promo_ends_at || $this->promo_ends_at->isFuture());

        return $promo ? $this->promo_price : $this->price;
    }

    public function getInStockAttribute(): bool
    {
        return ! $this->track_stock || ($this->inventory?->quantity ?? 0) > 0;
    }

    protected $fillable = ['business_unit_id', 'product_category_id', 'name', 'slug', 'sku', 'barcode', 'brand', 'size', 'description', 'short_description', 'highlights', 'cost_price', 'price', 'promo_price', 'promo_ends_at', 'unit', 'is_featured', 'is_age_restricted', 'track_stock', 'status'];

    protected function casts(): array
    {
        return ['cost_price' => 'decimal:2', 'price' => 'decimal:2', 'promo_price' => 'decimal:2', 'promo_ends_at' => 'datetime', 'is_featured' => 'boolean', 'is_age_restricted' => 'boolean', 'track_stock' => 'boolean', 'highlights' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    /** All gallery slides [url, alt]; a neutral local placeholder when no photo has been uploaded yet. */
    public function getGallerySlidesAttribute(): array
    {
        $slides = $this->images->map(fn ($i) => ['url' => Storage::disk('public')->url($i->path), 'alt' => $i->alt ?: $this->name, 'caption' => $i->alt])->values()->all();

        return $slides ?: [['url' => asset('images/placeholder.svg'), 'alt' => $this->name, 'placeholder' => true]];
    }

    public function getImageUrlAttribute(): string
    {
        return $this->gallery_slides[0]['url'];
    }

    public function getHoverImageUrlAttribute(): ?string
    {
        return $this->images->count() > 1 ? Storage::disk('public')->url($this->images[1]->path) : null;
    }

    /** Percentage off when a promo price is active, else 0. */
    public function getDiscountPercentAttribute(): int
    {
        return $this->current_price != $this->price && (float) $this->price > 0 ? (int) round((1 - (float) $this->current_price / (float) $this->price) * 100) : 0;
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
