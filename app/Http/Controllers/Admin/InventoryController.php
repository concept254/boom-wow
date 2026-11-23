<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InventoryController extends Controller
{
    private function gate(Request $request, string $ability = 'view'): void
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can("inventory.$ability"), 403);
    }

    public function index(Request $request)
    {
        $this->gate($request);
        $rows = Inventory::with('product.business')
            ->when($request->filter === 'low', fn ($q) => $q->whereColumn('quantity', '<=', 'reorder_level'))
            ->when($request->filter === 'out', fn ($q) => $q->where('quantity', 0))
            ->when($request->q, fn ($q, $t) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.addcslashes($t, '%_\\').'%')->orWhere('sku', 'like', '%'.addcslashes($t, '%_\\').'%')))
            ->orderBy('quantity')->paginate(15)->withQueryString();

        return view('admin.inventory.index', compact('rows'));
    }

    public function movements(Request $request)
    {
        $this->gate($request);
        $rows = StockMovement::with(['product', 'user', 'business'])
            ->when($request->product, fn ($q, $p) => $q->where('product_id', (int) $p))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.inventory.movements', compact('rows'));
    }

    public function adjust(Request $request, int $product, InventoryService $inventory)
    {
        $this->gate($request, 'update');
        $product = Product::findOrFail($product); // scoped to the user's businesses

        $data = $request->validate([
            'action' => ['required', 'in:receive,adjust,deduct'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            match ($data['action']) {
                'receive' => $inventory->receive($product, $data['quantity'], $data['reason'], $request->user()),
                'deduct' => $inventory->deduct($product, $data['quantity'], $data['reason'], $request->user()),
                'adjust' => $inventory->adjustTo($product, $data['quantity'], $data['reason'], $request->user()),
            };
            if (isset($data['reorder_level'])) {
                Inventory::where('product_id', $product->id)->update(['reorder_level' => $data['reorder_level']]);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        ActivityLogger::log('stock', "Stock {$data['action']} for {$product->name}", $product);

        return back()->with('status', 'Stock updated.');
    }
}
