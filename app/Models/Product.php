<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    public function getCurrentPriceAttribute(): string
    {
        $promo = $this->promo_price !== null && (! $this->promo_ends_at || $this->promo_ends_at->isFuture());

        return $promo ? $this->promo_price : $this->price;
    }

    public function getInStockAttribute(): bool
    {
        return ! $this->track_stock || ($this->inventory?->quantity ?? 0) > 0;
    }

    protected $fillable = ['business_unit_id', 'product_category_id', 'name', 'slug', 'sku', 'barcode', 'brand', 'size', 'description', 'cost_price', 'price', 'promo_price', 'promo_ends_at', 'unit', 'is_featured', 'is_age_restricted', 'track_stock', 'status'];

    protected function casts(): array
    {
        return ['cost_price' => 'decimal:2', 'price' => 'decimal:2', 'promo_price' => 'decimal:2', 'promo_ends_at' => 'datetime', 'is_featured' => 'boolean', 'is_age_restricted' => 'boolean', 'track_stock' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
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
