<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use Illuminate\Database\Seeder;

/**
 * Starter "Our story" text and key points for each business page. Generic placeholder copy to be edited
 * in Admin > Business Units. Only fills fields that are still empty, so it never overwrites real content.
 */
class BusinessPortalSeeder extends Seeder
{
    public function run(): void
    {
        $copy = [
            'salon' => [
                'About our salon',
                "Our salon is a relaxed, welcoming space where every visit is about you. From a quick trim to a full colour or styling session, our team takes the time to listen to what you want and to advise on what suits you best.\n\nWe use professional products and keep our standards of hygiene and service high, so you can sit back and enjoy the experience.",
                ['Experienced, friendly stylists', 'Professional hair and beauty products', 'Easy online booking', 'Clean, comfortable space'],
            ],
            'liquor-store' => [
                'About our liquor store',
                "Our liquor store stocks a wide range of beers, ciders, spirits and wines, from everyday favourites to special-occasion bottles. Our staff are happy to recommend something for your taste, your budget or your event.\n\nWe sell to adults only and ask that you always enjoy responsibly.",
                ['Wide range of well-known brands', 'Specials and offers every month', 'Order online and collect in store', 'Friendly, knowledgeable staff'],
            ],
            'it-consultants' => [
                'About our IT consulting',
                "We help people and businesses get the most from their technology. Whether you need a website, reliable support, or advice on the right systems for your team, we keep things simple, clear and tailored to your needs.\n\nTell us what you are trying to achieve and we will recommend a practical plan and a fair quote.",
                ['Clear advice in plain language', 'Websites, systems and support', 'Fair, upfront quotes', 'Responsive support when you need it'],
            ],
            'property-management' => [
                'About our property management',
                "We look after properties so owners and tenants do not have to worry. From finding the right tenant to handling maintenance and day-to-day questions, we aim to make renting and letting simple and transparent.\n\nGet in touch to view a property or to talk about managing yours.",
                ['Listings for rent and for sale', 'Viewings arranged for you', 'Maintenance and tenant support', 'Clear communication with owners'],
            ],
            'events-management' => [
                'About our events management',
                "We plan and run events that people remember, from birthdays and weddings to corporate functions and launches. We handle the details, from venue and suppliers to the schedule on the day, so you can enjoy your event.\n\nTell us your date, your guest numbers and your ideas, and we will put together a package that fits.",
                ['Planning from first idea to the day itself', 'Trusted venues and suppliers', 'Packages to suit your budget', 'A dedicated planner for your event'],
            ],
            'clothing' => [
                'About our clothing store',
                "Our clothing store is home to a street wear brand built on style, comfort and confidence. We design pieces that are easy to wear every day and made to stand out.\n\nBrowse the latest drops online, and ask us about sizing or exclusive items.",
                ['Street wear designed for everyday', 'New drops and limited pieces', 'Order online, collect or deliver', 'Help with sizing and styling'],
            ],
        ];

        foreach ($copy as $slug => [$title, $body, $points]) {
            $b = BusinessUnit::where('slug', $slug)->first();
            if (! $b) {
                continue;
            }
            $b->about_title = $b->about_title ?: $title;
            $b->about_body = $b->about_body ?: $body;
            if (empty($b->highlights)) {
                $b->highlights = $points;
            }
            $b->save();
        }
    }
}
