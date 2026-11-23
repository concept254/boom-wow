<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BusinessUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'tagline', 'description', 'logo_path', 'cover_path', 'email', 'phone',
        'address', 'city', 'latitude', 'longitude', 'social_links', 'accepts_bookings', 'sells_products',
        'age_restricted', 'is_featured', 'status', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'accepts_bookings' => 'boolean',
            'sells_products' => 'boolean',
            'age_restricted' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : 'https://picsum.photos/seed/'.$this->slug.'/1600/600';
    }

    /** Today's opening-hours row (needs the `hours` relation; eager-load it for lists). */
    public function todayHours(): ?BusinessHour
    {
        return $this->hours->firstWhere('day_of_week', now()->dayOfWeek);
    }

    /** true = open now, false = closed, null = no hours recorded. */
    public function isOpenNow(): ?bool
    {
        $h = $this->todayHours();
        if (! $h) {
            return null;
        }
        if ($h->is_closed || ! $h->opens_at || ! $h->closes_at) {
            return false;
        }
        $now = now()->format('H:i:s');

        return $now >= $h->opens_at && $now < $h->closes_at;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')->withTimestamps();
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderBy('day_of_week');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }
}
