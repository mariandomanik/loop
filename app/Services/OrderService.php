<?php

namespace App\Services;

use App\Models\Order;
use App\Services\PaymentProviders\SuperPayment;

class OrderService
{
    public const PAYMENT_PROVIDER_SUPER_PAYMENT = 'super_payment';

    /**
     * @return array{order: Order, message: string}
     *
     * @throws \Exception
     */
    public function pay(Order $order, string $paymentProviderName): array
    {
        if ($order->is_paid) {
            return [
                'order' => $order,
                'message' => 'Order is already paid',
            ];
        }

        $paymentProvider = match ($paymentProviderName) {
            self::PAYMENT_PROVIDER_SUPER_PAYMENT => new SuperPayment($order),
            default => throw new \Exception('Payment provider not found'),
        };

        return $paymentProvider->pay();
    }
}
