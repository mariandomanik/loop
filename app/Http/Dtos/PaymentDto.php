<?php

namespace App\Http\Dtos;

class PaymentDto
{
    public int $orderId;

    public string $customerEmail;

    public float $value;

    public function __construct(int $order_id, string $customer_email, float $value)
    {
        $this->orderId = $order_id;
        $this->customerEmail = $customer_email;
        $this->value = $value;
    }

    /**
     * @return array{order_id: int, customer_email: string, value: float}
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_email' => $this->customerEmail,
            'value' => $this->value,
        ];
    }
}
