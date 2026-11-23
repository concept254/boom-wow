<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['business', 'images', 'inventory', 'category'])->published()
            ->when($request->business, fn ($q, $b) => $q->whereHas('business', fn ($x) => $x->where('slug', $b)))
            ->when($request->q, function ($q, $t) {
                $like = '%'.addcslashes($t, '%_\\').'%';
                $q->where(fn ($x) => $x->where('name', 'like', $like)->orWhere('brand', 'like', $like));
            })
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('public.products', ['products' => $products, 'businesses' => BusinessUnit::published()->where('sells_products', true)->get()]);
    }

    public function show(BusinessUnit $business, string $slug)
    {
        $product = $business->products()->with(['images', 'inventory', 'category'])->published()->where('slug', $slug)->firstOrFail();

        return view('public.product', ['product' => $product, 'business' => $business]);
    }

    public function cart(CartService $cart) { return view('public.cart', ['lines' => $cart->lines(), 'total' => $cart->total()]); }

    public function add(Request $request, CartService $cart, Product $product)
    {
        abort_unless($product->status === 'published', 404);
        $cart->add($product, (int) $request->input('quantity', 1));

        return redirect()->route('cart')->with('status', "{$product->name} added to your cart.");
    }

    public function update(Request $request, CartService $cart, Product $product)
    {
        $cart->update($product->id, (int) $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']])['quantity']);

        return back();
    }

    public function remove(CartService $cart, Product $product)
    {
        $cart->remove($product->id);

        return back();
    }

    public function checkout(CartService $cart)
    {
        $lines = $cart->lines();
        abort_if($lines->isEmpty(), 404);
        $ageRestricted = $lines->contains(fn ($l) => $l['product']->is_age_restricted || $l['product']->business->age_restricted);

        return view('public.checkout', ['lines' => $lines, 'total' => $cart->total(), 'ageRestricted' => $ageRestricted]);
    }

    public function place(Request $request, CartService $cart, OrderService $orders)
    {
        $data = $request->validate([
            'fulfilment' => ['required', 'in:collection,delivery'],
            'phone' => ['nullable', 'string', 'max:30'],
            'delivery_address' => ['required_if:fulfilment,delivery', 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'age_confirmed' => ['nullable', 'boolean'],
        ]);

        try {
            $order = $orders->place($request->user(), $cart->raw(), $data);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['cart' => $e->getMessage()]);
        }

        $cart->clear();

        return redirect()->route('portal.orders.show', $order)->with('status', "Order {$order->order_number} placed. We'll confirm payment details shortly.");
    }
}
