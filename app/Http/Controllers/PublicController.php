<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BusinessUnit;
use App\Models\CmsPage;
use App\Models\Enquiry;
use App\Models\Event;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Subscriber;
use App\Models\TeamMember;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Property;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\EnquiryReceivedNotification;
use App\Services\HeroImageService;
use App\Services\SearchService;
use App\Services\SettingsService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PublicController extends Controller
{
    public function home(HeroImageService $hero)
    {
        $heroImage = $hero->pick();

        // Slides come from Admin > Hero Slides. With none published, show one slide built from the hero settings.
        $slides = HeroSlide::active()->limit(8)->get();
        if ($slides->isEmpty()) {
            $slides = collect([new HeroSlide([
                'caption' => 'Boom & Wow Holdings',
                'heading' => SettingsService::get('hero_title', 'One Destination. Endless Possibilities.'),
                'subheading' => SettingsService::get('hero_subtitle', 'Discover exceptional services, products and experiences under one roof.'),
                'cta_label' => 'Explore our businesses', 'cta_url' => '/businesses',
                'cta2_label' => 'Contact us', 'cta2_url' => '/contact',
            ])]);
        }

        $businesses = BusinessUnit::published()->with('hours')->orderBy('sort_order')->get();
        $about = CmsPage::published()->where('slug', 'about-us')->first();

        return view('public.home', [
            'heroImage' => $heroImage,
            'slides' => $slides,
            'about' => $about,
            'aboutExcerpt' => Str::limit(trim(preg_split('/\n\s*\n/', (string) ($about->content ?? ''))[0] ?? ''), 380)
                ?: 'Boom & Wow Holdings brings together '.\Illuminate\Support\Arr::join($businesses->pluck('name')->map(fn ($n) => Str::lower($n))->all(), ', ', ' and ').' - all served by one team and one customer account.',
            'businesses' => $businesses,
            'stores' => $businesses->where('is_featured', true)->values(),
            'stats' => [
                'Businesses' => $businesses->count(),
                'Services' => Service::published()->count(),
                'Products' => Product::published()->count(),
                'Upcoming events' => Event::published()->where('starts_at', '>=', now())->count(),
            ],
            'services' => Service::with(['business', 'images'])->withRatings()->published()->where('is_featured', true)->limit(6)->get(),
            'products' => Product::with(['business', 'images'])->withRatings()->published()->where('is_featured', true)->limit(8)->get(),
            'faqs' => Faq::published()->orderBy('sort_order')->limit(6)->get(),
            'testimonials' => Testimonial::published()->latest()->limit(3)->get(),
        ]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:150'],
            'website' => ['prohibited'], // honeypot
        ]);

        $sub = Subscriber::firstOrNew(['email' => strtolower($data['email'])]);
        $sub->fill(['name' => $data['name'] ?? $sub->name, 'is_active' => true, 'subscribed_at' => $sub->subscribed_at ?? now()])->save();

        // Same message whether or not the address already existed (does not reveal who is subscribed).
        return back()->with('status', 'Thanks for subscribing - watch your inbox for specials and news.');
    }

    public function page(string $slug)
    {
        $page = CmsPage::published()->where('slug', $slug)->firstOrFail();

        return view('public.page', compact('page'));
    }

    public function about()
    {
        $page = CmsPage::published()->where('slug', 'about-us')->first();
        $businesses = BusinessUnit::published()->orderBy('sort_order')->get();

        return view('public.about', [
            'page' => $page,
            'paragraphs' => array_values(array_filter(preg_split('/\n\s*\n/', (string) ($page->content ?? '')) ?: [], fn ($p) => trim($p) !== '')),
            'businesses' => $businesses,
            'management' => TeamMember::published()->where('team_group', 'management')->orderBy('sort_order')->get(),
            'team' => TeamMember::published()->where('team_group', 'team')->orderBy('sort_order')->get(),
            'stats' => [
                'Businesses' => $businesses->count(),
                'Services' => Service::published()->count(),
                'Products' => Product::published()->count(),
                'Team members' => TeamMember::published()->count(),
            ],
            'faqs' => Faq::published()->orderBy('sort_order')->limit(6)->get(),
            'testimonials' => Testimonial::published()->latest()->limit(3)->get(),
        ]);
    }
    public function privacy() { return $this->page('privacy-policy'); }
    public function terms() { return $this->page('terms-and-conditions'); }

    public function faqs() { return view('public.faqs', ['faqs' => Faq::published()->orderBy('sort_order')->get()]); }

    public function businesses() { return view('public.businesses', ['businesses' => BusinessUnit::published()->orderBy('sort_order')->get()]); }

    public function business(BusinessUnit $business)
    {
        abort_unless($business->status === 'published', 404);
        $business->load(['hours', 'images', 'staff' => fn ($q) => $q->where('is_active', true)]);

        return view('public.business', [
            'business' => $business,
            'services' => $business->services()->published()->orderBy('name')->get(),
            'products' => $business->sells_products ? $business->products()->with(['images', 'inventory'])->withRatings()->published()->limit(12)->get() : collect(),
            'properties' => $business->properties()->published()->latest()->limit(6)->get(),
            'events' => $business->events()->published()->where('starts_at', '>=', now())->orderBy('starts_at')->limit(6)->get(),
            'portfolio' => $business->portfolioItems()->published()->limit(6)->get(),
            'packages' => $business->packages()->where('status', 'published')->get(),
            'posts' => $business->show_articles ? $business->posts()->published()->latest('published_at')->limit(3)->get() : collect(),
            'postCount' => $business->show_articles ? $business->posts()->published()->count() : 0,
        ]);
    }

    public function services(Request $request)
    {
        $services = Service::with(['business', 'images'])->withRatings()->published()
            ->when($request->business, fn ($q, $b) => $q->whereHas('business', fn ($x) => $x->where('slug', $b)))
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', '%'.addcslashes($t, '%_\\').'%'))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('public.services', ['services' => $services, 'businesses' => BusinessUnit::published()->get()]);
    }

    public function service(Request $request, Service $service)
    {
        abort_unless($service->status === 'published', 404);
        $service->load(['business', 'images', 'staff']);
        $user = $request->user();

        $related = Service::with(['business', 'images'])->withRatings()->published()
            ->where('id', '!=', $service->id)
            ->orderByRaw('CASE WHEN service_category_id = ? THEN 0 WHEN business_unit_id = ? THEN 1 ELSE 2 END', [$service->service_category_id ?? 0, $service->business_unit_id])
            ->orderByDesc('is_featured')->latest()->limit(4)->get();

        return view('public.service', [
            'service' => $service, 'business' => $service->business, 'related' => $related,
            'stats' => $service->reviewStats(),
            'reviews' => $service->reviews()->where('status', 'published')->latest()->limit(20)->get(),
            'userReview' => $user ? $service->reviews()->where('user_id', $user->id)->first() : null,
            'verified' => $user ? ReviewController::isVerified($user->id, 'service', $service->id) : false,
        ]);
    }

    public function events() { return view('public.events', ['events' => Event::with('business')->published()->where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at')->paginate(12)]); }

    public function properties(Request $request)
    {
        $props = Property::with('business')->published()
            ->when($request->listing, fn ($q, $l) => $q->where('listing_type', $l))
            ->latest()->paginate(12)->withQueryString();

        return view('public.properties', ['properties' => $props]);
    }

    public function property(Property $property)
    {
        abort_unless($property->status === 'published', 404);

        return view('public.property', ['property' => $property->load('business')]);
    }

    public function event(Event $event)
    {
        abort_unless($event->status === 'published', 404);

        return view('public.event', ['event' => $event->load('business')]);
    }

    public function search(Request $request, SearchService $search)
    {
        $term = trim((string) $request->query('q', ''));

        return view('public.search', [
            'term' => $term,
            'results' => strlen($term) >= 2 ? $search->search($term) : [],
        ]);
    }

    public function contact()
    {
        $lat = (float) SettingsService::get('contact_lat', -26.1076);
        $lng = (float) SettingsService::get('contact_lng', 28.0567);

        return view('public.contact', [
            'businesses' => BusinessUnit::published()->with('hours')->orderBy('sort_order')->get(),
            'faqs' => Faq::published()->orderBy('sort_order')->limit(5)->get(),
            'lat' => $lat,
            'lng' => $lng,
            // OpenStreetMap embed: no API key or account needed.
            'mapSrc' => sprintf('https://www.openstreetmap.org/export/embed.html?bbox=%s&layer=mapnik&marker=%s', implode('%2C', [round($lng - 0.012, 6), round($lat - 0.007, 6), round($lng + 0.012, 6), round($lat + 0.007, 6)]), $lat.'%2C'.$lng),
            'directionsUrl' => 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng,
        ]);
    }

    /** Contact page, storefront and property/event enquiry forms all post here. */
    public function storeEnquiry(Request $request)
    {
        $data = $request->validate([
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'type' => ['required', 'in:general,project,support,viewing,event'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject_line' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
            'subject_type' => ['nullable', 'in:property,event'],
            'subject_id' => ['nullable', 'integer'],
            'website' => ['prohibited'], // honeypot
        ]);

        $subjectClass = match ($data['subject_type'] ?? null) { 'property' => Property::class, 'event' => Event::class, default => null };
        $enquiry = Enquiry::create([
            ...collect($data)->except(['subject_type', 'subject_id', 'website'])->all(),
            'user_id' => $request->user()?->id,
            'subject_type' => $subjectClass,
            'subject_id' => $subjectClass ? $data['subject_id'] : null,
        ]);

        $recipients = User::role('Super Admin')->get();
        if ($enquiry->business_unit_id) {
            $recipients = $recipients->merge($enquiry->business->users()->role('Business Manager')->get())->unique('id');
        }
        Notification::send($recipients, new EnquiryReceivedNotification($enquiry));

        return back()->with('status', 'Thank you - your enquiry has been received. We will be in touch shortly.');
    }

    public function blog(Request $request)
    {
        $filter = $request->filled('business') ? BusinessUnit::published()->where('slug', $request->string('business'))->first() : null;

        return view('public.blog', [
            'posts' => BlogPost::with('business')->published()->when($filter, fn ($q) => $q->where('business_unit_id', $filter->id))->latest('published_at')->paginate(9)->withQueryString(),
            'filter' => $filter,
            'businesses' => BusinessUnit::published()->orderBy('sort_order')->get(['id', 'name', 'slug']),
        ]);
    }

    public function blogPost(string $slug)
    {
        $post = BlogPost::with('business')->published()->where('slug', $slug)->firstOrFail();

        return view('public.blog-post', [
            'post' => $post,
            'more' => BlogPost::with('business')->published()->where('id', '!=', $post->id)
                ->when($post->business_unit_id, fn ($q) => $q->where('business_unit_id', $post->business_unit_id))
                ->latest('published_at')->limit(3)->get(),
        ]);
    }
}
