<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('boomwow.admin');
        $password = $cfg['password'] ?: Str::password(16);
        [$first, $last] = array_pad(explode(' ', $cfg['name'], 2), 2, 'Admin');

        $user = User::firstOrCreate(['email' => $cfg['email']], [
            'first_name' => $first, 'last_name' => $last, 'password' => $password, 'email_verified_at' => now(),
        ]);
        $user->assignRole('Super Admin');

        if ($user->wasRecentlyCreated && ! $cfg['password'] && $this->command) {
            $this->command->warn("ADMIN_PASSWORD was empty. Generated one-time password for {$cfg['email']}: {$password}");
        }
    }
}
