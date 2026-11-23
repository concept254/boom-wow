<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = ['business_unit_id', 'title', 'slug', 'property_type', 'listing_type', 'description', 'address', 'suburb', 'city', 'price', 'bedrooms', 'bathrooms', 'floor_area', 'image_path', 'gallery', 'availability', 'available_from', 'is_featured', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'gallery' => 'array', 'available_from' => 'date', 'is_featured' => 'boolean'];
    }
}
