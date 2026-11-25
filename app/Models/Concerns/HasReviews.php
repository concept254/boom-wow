<?php

namespace App\Models\Concerns;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** Reviews + rating summary for Product and Service. Only published reviews are ever counted. */
trait HasReviews
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /** Adds rating_avg and rating_count (published only) so product/service cards need no extra queries. */
    public function scopeWithRatings(Builder $query): Builder
    {
        $published = fn ($q) => $q->where('status', 'published');

        return $query->withAvg(['reviews as rating_avg' => $published], 'rating')->withCount(['reviews as rating_count' => $published]);
    }

    /** @return array{avg: float, count: int, dist: array<int,int>} */
    public function reviewStats(): array
    {
        $rows = $this->reviews()->where('status', 'published')->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating');
        $dist = [];
        foreach (range(5, 1) as $star) {
            $dist[$star] = (int) ($rows[$star] ?? 0);
        }
        $count = array_sum($dist);
        $avg = $count ? array_sum(array_map(fn ($star, $n) => $star * $n, array_keys($dist), $dist)) / $count : 0.0;

        return ['avg' => round($avg, 1), 'count' => $count, 'dist' => $dist];
    }
}
