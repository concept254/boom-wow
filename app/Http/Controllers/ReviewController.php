<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;

/** Customers review products they bought and services they booked. Reviews wait for approval in the admin. */
class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:product,service'],
            'id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $item = $data['type'] === 'product'
            ? Product::published()->findOrFail($data['id'])
            : Service::published()->findOrFail($data['id']);
        $user = $request->user();

        Review::updateOrCreate(
            ['reviewable_type' => $item->getMorphClass(), 'reviewable_id' => $item->getKey(), 'user_id' => $user->id],
            [
                'business_unit_id' => $item->business_unit_id,
                'name' => $user->name,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'is_verified' => self::isVerified($user->id, $data['type'], $item->getKey()),
                'status' => 'pending', // always re-checked, including when a customer edits an earlier review
            ],
        );

        return redirect(url()->previous().'#reviews')->with('status', 'Thank you! Your review will appear once our team has approved it.');
    }

    /** Did this customer actually buy the product / book the service? */
    public static function isVerified(int $userId, string $type, int $id): bool
    {
        if ($type === 'product') {
            return OrderItem::where('product_id', $id)->whereHas('order', fn ($q) => $q->where('user_id', $userId)->where('status', '!=', 'cancelled'))->exists();
        }

        return Booking::where('service_id', $id)->where('user_id', $userId)->where('status', 'completed')->exists();
    }
}
