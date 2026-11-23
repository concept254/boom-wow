<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\CustomerProfile;
use App\Models\Enquiry;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Demo staff-side accounts. Password is for LOCAL DEMO ONLY - never seed this in production.
        $pw = 'DemoPass123';

        $customers = collect([['Sipho', 'Ndlovu'], ['Amanda', 'Pretorius'], ['Kabelo', 'Sithole']])->map(function ($n, $i) use ($pw) {
            $u = User::firstOrCreate(['email' => strtolower($n[0]).'@example.com'], ['first_name' => $n[0], 'last_name' => $n[1], 'phone' => '082 000 000'.$i, 'password' => $pw, 'email_verified_at' => now()]);
            $u->assignRole('Customer');
            CustomerProfile::firstOrCreate(['user_id' => $u->id]);

            return $u;
        });

        foreach (BusinessUnit::all() as $b) {
            $m = User::firstOrCreate(['email' => "manager.{$b->slug}@example.com"], ['first_name' => 'Manager', 'last_name' => $b->name, 'password' => $pw, 'email_verified_at' => now()]);
            $m->assignRole('Business Manager');
            $m->businesses()->syncWithoutDetaching([$b->id]);
        }
        $salon = BusinessUnit::where('slug', 'salon')->first();
        $staff = User::firstOrCreate(['email' => 'staff.salon@example.com'], ['first_name' => 'Salon', 'last_name' => 'Staff', 'password' => $pw, 'email_verified_at' => now()]);
        $staff->assignRole('Staff');
        $staff->businesses()->syncWithoutDetaching([$salon->id]);

        // Bookings through the real service so slots + history are created.
        $svc = Service::where('business_unit_id', $salon->id)->where('slug', 'signature-facial')->first();
        $it = Service::whereHas('business', fn ($q) => $q->where('slug', 'it-consultants'))->first();
        $day = now()->addDay();
        while (! app(BookingService::class)->isDateOpen($salon, $day)) {
            $day = $day->addDay();
        }
        foreach ([[$customers[0], $svc, '10:00'], [$customers[1], $svc, '14:00']] as [$c, $s, $t]) {
            try {
                app(BookingService::class)->book($c, $s, $day->copy()->setTimeFromTimeString($t));
            } catch (\Throwable) {
            }
        }
        try {
            app(BookingService::class)->book($customers[2], $it, $day->copy()->setTimeFromTimeString('11:00'));
        } catch (\Throwable) {
        }

        Enquiry::firstOrCreate(['email' => 'lindiwe@example.com', 'type' => 'project'], ['business_unit_id' => BusinessUnit::where('slug', 'it-consultants')->value('id'), 'name' => 'Lindiwe Zulu', 'phone' => '083 111 2222', 'subject_line' => 'New office network', 'message' => 'We are moving offices next quarter and need a quote for networking and Wi-Fi.', 'status' => 'new']);
        Enquiry::firstOrCreate(['email' => 'marcus@example.com', 'type' => 'viewing'], ['business_unit_id' => BusinessUnit::where('slug', 'property-management')->value('id'), 'name' => 'Marcus Venter', 'subject_line' => 'Viewing request', 'message' => 'I would like to view the 2-bedroom apartment this Saturday.', 'status' => 'new']);
        Enquiry::firstOrCreate(['email' => 'zanele@example.com', 'type' => 'event'], ['business_unit_id' => BusinessUnit::where('slug', 'events-management')->value('id'), 'name' => 'Zanele Mthembu', 'subject_line' => 'Wedding enquiry', 'message' => 'Looking for a Premium wedding package for 120 guests next spring.', 'status' => 'in_progress']);
    }
}
