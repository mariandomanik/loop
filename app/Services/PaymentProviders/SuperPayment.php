<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentInterface;
use App\Http\Dtos\PaymentDto;
use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperPayment implements PaymentInterface
{
    protected const URL = 'https://superpay.view.agentur-loop.com/pay';

    protected const MSG_RESPONSE_OK = 'Payment Successful';

    protected const MSG_RESPONSE_INSUFFICIENT_FUNDS = 'Insufficient Funds';

    protected const MSG_SERVICE_UNAVAILABLE = 'Service Unavailable';

    protected const MSG_MAX_ATTEMPTS = 'Max attempts reached';

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return array{order: Order, message: string}
     */
    public function pay(): array
    {
        Log::info('Sending payment request', [
            'order_id' => $this->order->id,
            'customer_email' => $this->order->customer->email,
            'value' => $this->order->paymentTotal,
        ]);

        $paymentDto = new PaymentDto(
            $this->order->id,
            $this->order->customer->email,
            $this->order->paymentTotal
        );

        try {
            $response = $this->sendRequest($paymentDto);
            $body = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
            $this->order->is_paid = $this->processResponse($body);
            $this->order->save();

            return [
                'order' => $this->order,
                'message' => $body['message'],
            ];

        } catch (\Throwable $th) {
            Log::error('Error while processing payment', [
                'order_id' => $this->order->id,
                'message' => $th->getMessage(),
            ]);
            $this->order->is_paid = false;
            $this->order->save();

            return [
                'order' => $this->order,
                'message' => self::MSG_SERVICE_UNAVAILABLE,
            ];
        }
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function processResponse(array $body): bool
    {
        if (! isset($body['message'])) {
            Log::error('Error while processing payment', [
                'order_id' => $this->order->id,
                'response' => $body,
            ]);

            throw new \Exception('Response body does not contain message');
        }

        return $body['message'] === self::MSG_RESPONSE_OK;
    }

    /**
     * @throws \HttpException
     */
    public function sendRequest(PaymentDto $paymentDto): Response
    {
        $maxAttempts = 3;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::timeout(30)
                ->post(self::URL, [
                    'order_id' => $paymentDto->orderId,
                    'customer_email' => $paymentDto->customerEmail,
                    'value' => $paymentDto->value,
                ]);

            if ($response->status() === 200) {
                Log::info('Payment request successful', [
                    'order_id' => $this->order->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $response;
            }

            Log::error('Error while sending request to SuperPayment', [
                'order_id' => $this->order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $attempts++;
        }

        Log::error('Max retry attempts reached');

        throw new \HttpException(self::MSG_MAX_ATTEMPTS);
    }
}
