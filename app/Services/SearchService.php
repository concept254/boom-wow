<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\Event;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Property;
use App\Models\Service;
use App\Models\ServiceCategory;

class SearchService
{
    /** @return array<string,\Illuminate\Support\Collection> */
    public function search(string $term, int $limit = 8): array
    {
        $like = '%'.addcslashes(trim($term), '%_\\').'%';

        return [
            'businesses' => BusinessUnit::published()->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like))->limit($limit)->get(),
            'services' => Service::with('business')->published()->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)))->limit($limit)->get(),
            'products' => Product::with('business')->published()->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('brand', 'like', $like)->orWhere('description', 'like', $like)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)))->limit($limit)->get(),
            'events' => Event::with('business')->published()->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('venue', 'like', $like))->limit($limit)->get(),
            'properties' => Property::with('business')->published()->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('suburb', 'like', $like)->orWhere('city', 'like', $like))->limit($limit)->get(),
        ];
    }
}
