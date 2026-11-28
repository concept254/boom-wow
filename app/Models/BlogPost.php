<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $p) {
            if ($p->status === 'published' && ! $p->published_at) {
                $p->published_at = now();
            }
            if (blank($p->slug)) {
                $base = \Illuminate\Support\Str::slug((string) $p->title) ?: 'post';
                $slug = $base;
                for ($i = 2; static::withTrashed()->where('slug', $slug)->where('id', '!=', (int) $p->id)->exists(); $i++) {
                    $slug = $base.'-'.$i;
                }
                $p->slug = $slug;
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : null;
    }

    public function getReadMinutesAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 200));
    }

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
