<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use BelongsToBusiness;

    protected $table = 'inventory';

    protected $fillable = ['product_id', 'business_unit_id', 'quantity', 'reorder_level'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'reorder_level' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
