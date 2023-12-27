<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentInterface
{
    /**
     * @return array{order: Order, message: string}
     */
    public function pay(): array;

    /**
     * @param  array<string, mixed>  $body
     */
    public function processResponse(array $body): bool;
}
