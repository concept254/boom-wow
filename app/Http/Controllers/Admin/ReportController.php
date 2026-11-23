<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REPORTS = [
        'sales-by-business' => 'Sales by business',
        'sales-by-product' => 'Sales by product',
        'sales-by-date' => 'Sales by date',
        'inventory-levels' => 'Inventory levels',
        'stock-movements' => 'Stock movements',
        'low-stock' => 'Low stock',
        'booking-stats' => 'Booking statistics',
        'customer-registrations' => 'Customer registrations',
        'enquiry-stats' => 'Enquiry statistics',
    ];

    private function gate(Request $request): void
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can('reports.view'), 403);
    }

    public function index(Request $request)
    {
        $this->gate($request);

        return view('admin.reports.index', ['reports' => self::REPORTS]);
    }

    public function show(Request $request, string $report)
    {
        $this->gate($request);
        abort_unless(isset(self::REPORTS[$report]), 404);
        [$headings, $rows] = $this->build($request, $report);

        if ($request->query('export') === 'csv') {
            return $this->csv($report, $headings, $rows);
        }

        return view('admin.reports.show', ['title' => self::REPORTS[$report], 'report' => $report, 'headings' => $headings, 'rows' => $rows]);
    }

    private function ids(Request $request): ?array
    {
        return $request->user()->hasRole('Super Admin') ? null : $request->user()->businessIds();
    }

    /** @return array{0:array<int,string>,1:array<int,array>} */
    private function build(Request $request, string $report): array
    {
        $ids = $this->ids($request);
        $from = $request->query('from');
        $to = $request->query('to');
        $items = fn () => OrderItem::query()->withoutGlobalScopes()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')->where('orders.status', '!=', 'cancelled')
            ->when($ids !== null, fn ($q) => $q->whereIn('order_items.business_unit_id', $ids))
            ->when($from, fn ($q) => $q->whereDate('orders.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('orders.created_at', '<=', $to));

        switch ($report) {
            case 'sales-by-business':
                $r = $items()->join('business_units', 'business_units.id', '=', 'order_items.business_unit_id')
                    ->selectRaw('business_units.name n, COUNT(DISTINCT orders.id) o, SUM(order_items.quantity) u, SUM(order_items.subtotal) t')->groupBy('business_units.name')->orderByDesc('t')->get();

                return [['Business', 'Orders', 'Units', 'Sales (R)'], $r->map(fn ($x) => [$x->n, $x->o, $x->u, number_format($x->t, 2, '.', '')])->all()];
            case 'sales-by-product':
                $r = $items()->selectRaw('order_items.product_name n, order_items.sku s, SUM(order_items.quantity) u, SUM(order_items.subtotal) t')->groupBy('order_items.product_name', 'order_items.sku')->orderByDesc('t')->get();

                return [['Product', 'SKU', 'Units', 'Sales (R)'], $r->map(fn ($x) => [$x->n, $x->s, $x->u, number_format($x->t, 2, '.', '')])->all()];
            case 'sales-by-date':
                $r = $items()->selectRaw('DATE(orders.created_at) d, COUNT(DISTINCT orders.id) o, SUM(order_items.subtotal) t')->groupBy(DB::raw('DATE(orders.created_at)'))->orderBy('d')->get();

                return [['Date', 'Orders', 'Sales (R)'], $r->map(fn ($x) => [$x->d, $x->o, number_format($x->t, 2, '.', '')])->all()];
            case 'inventory-levels':
            case 'low-stock':
                $r = Inventory::with('product.business')->when($report === 'low-stock', fn ($q) => $q->whereColumn('quantity', '<=', 'reorder_level'))
                    ->when($ids !== null, fn ($q) => $q->whereIn('business_unit_id', $ids))->orderBy('quantity')->get();

                return [['Business', 'Product', 'SKU', 'Qty', 'Reorder level', 'Value at cost (R)'], $r->map(fn ($x) => [$x->product?->business?->name, $x->product?->name, $x->product?->sku, $x->quantity, $x->reorder_level, number_format($x->quantity * ($x->product?->cost_price ?? 0), 2, '.', '')])->all()];
            case 'stock-movements':
                $r = StockMovement::withoutGlobalScopes()->with(['product', 'user'])->when($ids !== null, fn ($q) => $q->whereIn('business_unit_id', $ids))
                    ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))->latest()->limit(5000)->get();

                return [['Date', 'Product', 'Type', 'Previous', 'Change', 'New', 'Reason', 'User'], $r->map(fn ($x) => [$x->created_at, $x->product?->name, $x->type, $x->previous_quantity, $x->change, $x->new_quantity, $x->reason, $x->user?->name])->all()];
            case 'booking-stats':
                $r = Booking::withoutGlobalScopes()->join('business_units', 'business_units.id', '=', 'bookings.business_unit_id')
                    ->when($ids !== null, fn ($q) => $q->whereIn('bookings.business_unit_id', $ids))
                    ->when($from, fn ($q) => $q->whereDate('bookings.starts_at', '>=', $from))->when($to, fn ($q) => $q->whereDate('bookings.starts_at', '<=', $to))
                    ->selectRaw('business_units.name n, bookings.status s, COUNT(*) c')->groupBy('business_units.name', 'bookings.status')->orderBy('n')->get();

                return [['Business', 'Status', 'Bookings'], $r->map(fn ($x) => [$x->n, $x->s, $x->c])->all()];
            case 'customer-registrations':
                abort_unless($ids === null, 403);
                $r = User::role('Customer')->selectRaw('DATE(created_at) d, COUNT(*) c')->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))->groupBy(DB::raw('DATE(created_at)'))->orderBy('d')->get();

                return [['Date', 'Registrations'], $r->map(fn ($x) => [$x->d, $x->c])->all()];
            case 'enquiry-stats':
                $r = Enquiry::withoutGlobalScopes()->leftJoin('business_units', 'business_units.id', '=', 'enquiries.business_unit_id')
                    ->when($ids !== null, fn ($q) => $q->whereIn('enquiries.business_unit_id', $ids))
                    ->selectRaw("COALESCE(business_units.name, 'General') n, enquiries.status s, COUNT(*) c")->groupBy('n', 'enquiries.status')->get();

                return [['Business', 'Status', 'Enquiries'], $r->map(fn ($x) => [$x->n, $x->s, $x->c])->all()];
        }

        abort(404);
    }

    private function csv(string $name, array $headings, array $rows): StreamedResponse
    {
        // Prefix cells that look like formulas so spreadsheets don't execute them (CSV injection).
        $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;

        return response()->streamDownload(function () use ($headings, $rows, $safe) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);
            foreach ($rows as $row) {
                fputcsv($out, array_map($safe, $row));
            }
            fclose($out);
        }, $name.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}
