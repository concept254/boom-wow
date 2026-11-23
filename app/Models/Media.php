<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['business_unit_id', 'uploaded_by', 'disk', 'path', 'original_name', 'mime_type', 'size', 'width', 'height', 'alt'];
}
