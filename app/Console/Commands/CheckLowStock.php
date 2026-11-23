<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';
    protected $description = 'Notify managers about low and out-of-stock products (daily digest)';

    public function handle(): int
    {
        $rows = Inventory::withoutGlobalScopes()->with('product.business')->whereColumn('quantity', '<=', 'reorder_level')->get();

        foreach ($rows as $row) {
            if (! $row->product) {
                continue;
            }
            $recipients = User::role('Super Admin')->get()
                ->merge($row->product->business->users()->role('Business Manager')->get())->unique('id');
            Notification::send($recipients, new LowStockNotification($row->product, $row->quantity));
        }

        $this->info("{$rows->count()} product(s) at or below reorder level.");

        return self::SUCCESS;
    }
}
