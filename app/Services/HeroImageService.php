<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class HeroImageService
{
    /** Returns a public URL for a random hero image, or the configured fallback. */
    public function pick(): string
    {
        $files = $this->files();

        if (empty($files)) {
            return $this->fallback();
        }

        return asset(config('boomwow.hero.directory').'/'.$files[array_rand($files)]);
    }

    /**
     * Fallback image URL. A "{seed}" token, or the seed segment of a picsum-style
     * ".../seed/<name>/..." URL, is replaced with a random value so the picture varies per visit.
     */
    public function fallback(): string
    {
        $url = (string) config('boomwow.hero.fallback_url');
        $seed = 'bw'.random_int(1, 40);

        if (str_contains($url, '{seed}')) {
            return str_replace('{seed}', $seed, $url);
        }

        return config('boomwow.hero.randomize_fallback', true)
            ? (preg_replace('#/seed/[^/]+/#', '/seed/'.$seed.'/', $url) ?? $url)
            : $url;
    }

    /** @return array<int,string> File names, cached so the directory is not scanned per request. */
    public function files(): array
    {
        return Cache::remember('hero_images', config('boomwow.hero.cache_seconds'), function () {
            $dir = public_path(config('boomwow.hero.directory'));

            if (! is_dir($dir)) {
                return [];
            }

            $allowed = config('boomwow.hero.extensions');

            return array_values(array_filter(
                scandir($dir) ?: [],
                fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowed, true),
            ));
        });
    }

    public function forget(): void
    {
        Cache::forget('hero_images');
    }
}
