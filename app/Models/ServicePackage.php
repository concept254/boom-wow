<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_unit_id', 'name', 'description', 'price', 'billing', 'features', 'status'];

    protected function casts(): array
    {
        return ['features' => 'array', 'price' => 'decimal:2'];
    }
}
