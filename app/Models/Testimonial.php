<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = ['business_unit_id', 'author_name', 'author_role', 'quote', 'rating', 'status'];
}
