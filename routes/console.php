<?php

use Illuminate\Support\Facades\Schedule;

// One cPanel cron entry drives everything: * * * * * php /home/ACCOUNT/boom-wow/artisan schedule:run
Schedule::command('bookings:send-reminders')->hourly();
Schedule::command('inventory:check-low-stock')->dailyAt('07:00');
