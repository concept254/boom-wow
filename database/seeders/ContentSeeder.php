<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Menu;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['about-us', 'About Us', "Boom & Wow Holdings is a diversified group of businesses united by one standard: exceptional service.\n\nFrom our salon and liquor store to IT consulting, property management and events, every business is run by people who care about the details.\n\nOne account gives you access to all of them."],
            ['privacy-policy', 'Privacy Policy', "We collect only the information needed to serve you: your contact details, bookings and orders.\n\nWe never sell your personal information. You may request access to, correction of or deletion of your data at any time, in line with POPIA.\n\nThis is a template - have it reviewed by a legal professional before launch."],
            ['terms-and-conditions', 'Terms and Conditions', "By using this website you agree to these terms.\n\nBookings and orders are subject to availability. Age-restricted products may only be sold to persons 18 or older.\n\nThis is a template - have it reviewed by a legal professional before launch."],
        ];
        foreach ($pages as [$slug, $title, $content]) {
            CmsPage::updateOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content, 'status' => 'published', 'published_at' => now()]);
        }

        foreach ([['Home', '/'], ['About Us', '/about-us'], ['Our Businesses', '/businesses'], ['Services', '/services'], ['Contact Us', '/contact']] as $i => [$l, $u]) {
            Menu::updateOrCreate(['location' => 'header', 'url' => $u], ['label' => $l, 'sort_order' => $i, 'is_active' => true]);
        }
        foreach ([['Privacy Policy', '/privacy-policy'], ['Terms & Conditions', '/terms-and-conditions'], ['FAQs', '/faqs'], ['News', '/news']] as $i => [$l, $u]) {
            Menu::updateOrCreate(['location' => 'footer', 'url' => $u], ['label' => $l, 'sort_order' => $i, 'is_active' => true]);
        }

        foreach ([['How do I book an appointment?', 'Create a free account, choose a business and service, then pick an available date and time.'], ['Can I order from several businesses at once?', 'Yes. One cart can hold products from every business; each order line keeps track of its business.'], ['How do I pay?', 'Online payment is coming soon. For now you can pay on collection or by arrangement.'], ['Do you deliver alcohol?', 'Delivery of alcohol is subject to licence conditions and age verification at the door.']] as $i => [$q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['answer' => $a, 'sort_order' => $i, 'status' => 'published']);
        }

        foreach ([['Nomsa P.', 'Salon client', 'The best blow-dry in Sandton, and booking online took a minute.'], ['Daniel R.', 'Director, Retail group', 'Their IT team rebuilt our store network without a day of downtime.'], ['Aisha & Imran', 'Wedding clients', 'Our wedding was seamless. Every detail was handled.']] as [$n, $r, $q]) {
            Testimonial::updateOrCreate(['author_name' => $n], ['author_role' => $r, 'quote' => $q, 'rating' => 5, 'status' => 'published']);
        }

        Promotion::updateOrCreate(['title' => 'Wine Weekend'], ['description' => 'Selected wines at special prices this month.', 'ends_on' => now()->addDays(14), 'status' => 'published']);
        Promotion::updateOrCreate(['title' => 'First Facial 15% Off'], ['description' => 'New salon clients enjoy 15% off a Signature Facial.', 'status' => 'published']);

        BlogPost::updateOrCreate(['slug' => 'welcome-to-boom-and-wow'], ['title' => 'Welcome to Boom & Wow', 'excerpt' => 'Our new home online.', 'body' => "We are delighted to launch our new website.\n\nBrowse our businesses, book appointments and order products with one account.", 'status' => 'published', 'published_at' => now()]);

        foreach ([
            'site_name' => 'Boom & Wow Holdings',
            'hero_title' => 'One Destination. Endless Possibilities.',
            'hero_subtitle' => 'Discover exceptional services, products and experiences under one roof. Welcome to Boom & Wow Holdings.',
            'contact_email' => 'info@example.com', 'contact_phone' => '+27 11 000 0000', 'contact_address' => '10 Rivonia Road, Sandton, Johannesburg',
            'footer_text' => '© Boom & Wow Holdings. All rights reserved.',
            'liquor_notice' => 'Enjoy responsibly. No sales to persons under 18. Do not drink and drive.',
        ] as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }
        cache()->forget('settings');
    }
}
