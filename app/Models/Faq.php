<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_unit_id', 'question', 'answer', 'sort_order', 'status'];
}
