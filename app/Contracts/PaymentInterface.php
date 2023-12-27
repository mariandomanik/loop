<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentInterface
{
    /**
     * @return array{order: Order, message: string}
     */
    public function pay(): array;

    public function processResponse(string $body): bool;
}
