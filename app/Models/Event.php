<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = ['business_unit_id', 'title', 'slug', 'description', 'venue', 'starts_at', 'ends_at', 'price', 'packages', 'capacity', 'image_path', 'is_featured', 'status'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'packages' => 'array', 'price' => 'decimal:2', 'is_featured' => 'boolean'];
    }
}
