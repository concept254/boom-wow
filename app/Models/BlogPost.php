<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    protected $fillable = ['business_unit_id', 'author_id', 'title', 'slug', 'excerpt', 'body', 'image_path', 'status', 'meta_title', 'meta_description', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
