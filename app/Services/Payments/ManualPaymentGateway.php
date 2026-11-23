<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;

class ManualPaymentGateway implements PaymentGateway
{
    public function __construct(private string $driver = 'manual') {}

    public function initiate(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'driver' => $this->driver, // manual | cash_on_collection | pending
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'ZAR',
        ]);
    }

    public function confirm(Payment $payment, ?string $providerReference = null): Payment
    {
        $payment->update(['status' => 'paid', 'paid_at' => now(), 'provider_reference' => $providerReference]);
        $payment->order->update(['payment_status' => 'paid']);

        return $payment;
    }
}
