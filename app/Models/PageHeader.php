<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/** Per-page banner settings (title, subtitle, background image). Every field is optional. */
class PageHeader extends Model
{
    public const KEYS = [
        'default' => 'Site-wide default (used by pages without their own image)',
        'businesses' => 'Our businesses', 'services' => 'Services', 'products' => 'Products', 'events' => 'Events',
        'properties' => 'Properties', 'blog' => 'News', 'faqs' => 'FAQs', 'about' => 'About us', 'contact' => 'Contact us',
        'search' => 'Search', 'cart' => 'Cart', 'checkout' => 'Checkout',
    ];

    protected $attributes = ['use_fallback' => true, 'is_active' => true];

    protected $fillable = ['key', 'title', 'subtitle', 'image_path', 'image_url', 'use_fallback', 'is_active'];

    protected function casts(): array
    {
        return ['use_fallback' => 'boolean', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('page_headers'));
        static::deleted(fn () => Cache::forget('page_headers'));
    }

    /** The active row for a key, or null. One cached query serves every page. */
    public static function for(?string $key): ?self
    {
        if (! $key) {
            return null;
        }
        $all = Cache::remember('page_headers', 600, fn () => static::where('is_active', true)->get()->keyBy('key')->all());

        return $all[$key] ?? null;
    }

    /** This row's own image (upload first, then URL), or null. */
    public function ownImage(): ?string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        return $this->image_url ?: null;
    }
}
