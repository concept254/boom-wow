<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/** Session-backed multi-business cart: [product_id => quantity]. */
class CartService
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Product $product, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$product->id] = min(99, ($cart[$product->id] ?? 0) + max(1, $qty));
        session([self::KEY => $cart]);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = min(99, $qty);
        }
        session([self::KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $this->update($productId, 0);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return Collection<int,array{product:Product,quantity:int,unit_price:string,line_total:string}> */
    public function lines(): Collection
    {
        $products = Product::with(['business', 'inventory', 'images'])->published()->where('price_on_request', false)->whereIn('id', array_keys($this->raw()))->get()->keyBy('id');

        return collect($this->raw())->map(function ($qty, $id) use ($products) {
            $p = $products->get($id);

            return $p ? [
                'product' => $p,
                'quantity' => $qty,
                'unit_price' => $p->current_price,
                'line_total' => number_format($p->current_price * $qty, 2, '.', ''),
            ] : null;
        })->filter()->values();
    }

    public function total(): string
    {
        return number_format($this->lines()->sum(fn ($l) => $l['line_total']), 2, '.', '');
    }
}
