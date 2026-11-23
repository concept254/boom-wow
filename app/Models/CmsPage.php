<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPage extends Model
{
    use SoftDeletes;

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    protected $fillable = ['title', 'slug', 'content', 'sections', 'status', 'meta_title', 'meta_description', 'published_at'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'published_at' => 'datetime'];
    }
}
