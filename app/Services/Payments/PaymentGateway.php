<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;

/**
 * Payment abstraction. Orders only ever talk to this interface, so adding
 * PayFast / PayGate / Ozow later means writing one new class and binding it
 * in AppServiceProvider - the cart and order modules stay untouched.
 */
interface PaymentGateway
{
    /** Create the payment record for an order and return it. */
    public function initiate(Order $order): Payment;

    /** Mark a payment as settled (called by admin or a gateway webhook). */
    public function confirm(Payment $payment, ?string $providerReference = null): Payment;
}
