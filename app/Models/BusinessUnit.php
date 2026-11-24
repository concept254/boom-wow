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
        'name', 'slug', 'type', 'enquiry_type', 'tagline', 'description', 'logo_path', 'cover_path', 'email', 'phone',
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

    protected static function booted(): void
    {
        // Adding a business should need nothing but a name: build the slug and sensible opening hours for it.
        static::saving(function (self $b) {
            if (blank($b->slug)) {
                $base = \Illuminate\Support\Str::slug((string) $b->name) ?: 'business';
                $slug = $base;
                for ($i = 2; static::withTrashed()->where('slug', $slug)->where('id', '!=', (int) $b->id)->exists(); $i++) {
                    $slug = $base.'-'.$i;
                }
                $b->slug = $slug;
            }
        });
        static::created(function (self $b) {
            foreach (range(0, 6) as $dow) {
                BusinessHour::firstOrCreate(['business_unit_id' => $b->id, 'day_of_week' => $dow], match (true) {
                    $dow === 0 => ['is_closed' => true],
                    $dow === 6 => ['is_closed' => false, 'opens_at' => '09:00', 'closes_at' => '14:00'],
                    default => ['is_closed' => false, 'opens_at' => '08:30', 'closes_at' => '17:30'],
                });
            }
        });
    }

    /** Free-text category label; the column is NOT NULL so an empty form value becomes "general". */
    public function setTypeAttribute($value): void
    {
        $this->attributes['type'] = filled($value) ? $value : 'general';
    }

    public function getEnquiryKindAttribute(): string
    {
        return $this->enquiry_type ?: 'general';
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
