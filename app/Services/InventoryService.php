<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Apply a signed stock change atomically. Throws if stock would go negative.
     * Must be callable inside an outer transaction (nested transactions are safe).
     */
    public function move(Product $product, int $change, string $type, ?string $reason = null, ?User $user = null, $reference = null): StockMovement
    {
        return DB::transaction(function () use ($product, $change, $type, $reason, $user, $reference) {
            $inventory = Inventory::withoutGlobalScopes()->lockForUpdate()->firstOrCreate(
                ['product_id' => $product->id],
                ['business_unit_id' => $product->business_unit_id, 'quantity' => 0, 'reorder_level' => 5],
            );

            $previous = $inventory->quantity;
            $new = $previous + $change;

            if ($new < 0) {
                throw new InvalidArgumentException("Insufficient stock for {$product->name}: {$previous} available, {$change} requested.");
            }

            $inventory->update(['quantity' => $new]);

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'business_unit_id' => $product->business_unit_id,
                'type' => $type,
                'previous_quantity' => $previous,
                'change' => $change,
                'new_quantity' => $new,
                'reason' => $reason,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'user_id' => $user?->id,
            ]);

            if ($new <= $inventory->reorder_level && $previous > $inventory->reorder_level) {
                $this->alert($product, $new);
            }

            return $movement;
        });
    }

    public function receive(Product $p, int $qty, ?string $reason = null, ?User $u = null): StockMovement
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Receive quantity must be positive.');
        }

        return $this->move($p, $qty, 'receive', $reason ?? 'Stock received', $u);
    }

    public function adjustTo(Product $p, int $target, string $reason, ?User $u = null): StockMovement
    {
        $current = (int) ($p->inventory?->quantity ?? 0);

        return $this->move($p, $target - $current, 'adjust', $reason, $u);
    }

    public function deduct(Product $p, int $qty, string $reason, ?User $u = null): StockMovement
    {
        return $this->move($p, -abs($qty), 'deduct', $reason, $u);
    }

    private function alert(Product $product, int $quantity): void
    {
        $recipients = User::role(['Super Admin'])->get()
            ->merge($product->business->users()->role('Business Manager')->get())
            ->unique('id');

        Notification::send($recipients, new LowStockNotification($product, $quantity));
    }
}
