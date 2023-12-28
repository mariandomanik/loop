<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws \JsonException
     */
    public function test_create_order(): void
    {
        $order = $this->createOrder()->first();

        $response = $this->postJson(route('orders.store'), [
            'customer_id' => $order->customer_id,
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'customer_id' => $order->customer_id,
                    'is_paid' => false,
                    'products' => [],
                ],
            ])
            ->content();

        $orderId = json_decode($response, false, 512, JSON_THROW_ON_ERROR)
                ->data
                ->id;

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'customer_id' => $order->customer_id,
            'is_paid' => 0,
        ]);
    }

    public function test_show_order(): void
    {
        $order = Order::factory()->create();

        $this->getJson(route('orders.show', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => $order->is_paid,
                    'products' => [],
                ],
            ]);
    }

    public function test_delete_order(): void
    {
        $order = Order::factory()->create();

        $this->deleteJson(route('orders.destroy', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Order deleted',
            ]);
    }

    /**
     * @throws \JsonException
     */
    public function test_order_index(): void
    {
        $orders = Order::factory()->count(50)->create();

        $response = $this->getJson(route('orders.index'))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $orders[0]->id,
                        'customer_id' => $orders[0]->customer_id,
                        'is_paid' => $orders[0]->is_paid,
                        'products' => [],
                    ],
                    [
                        'id' => $orders[1]->id,
                        'customer_id' => $orders[1]->customer_id,
                        'is_paid' => $orders[1]->is_paid,
                        'products' => [],
                    ],
                ],
            ])->content();

        $this->assertCount(
            15,
            json_decode($response, false, 512, JSON_THROW_ON_ERROR)->data
        );
    }

    public function test_update_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => false,
        ]);

        $this->putJson(route('orders.update', ['order' => $order->id, 'is_paid' => true]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'customer_id' => 1,
                    'is_paid' => false,
                    'products' => [],
                ],
            ]);
    }

    /**
     * @throws \JsonException
     */
    public function test_add_product_to_order(): void
    {
        $product1 = Product::factory()->create();
        $order = Order::factory()->create();

        $this->postJson(route('orders.add-product', ['order' => $order->id]), [
            'product_id' => $product1->id,
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => $order->is_paid,
                    'products' => [
                        [
                            'id' => $product1->id,
                            'product_name' => $product1->product_name,
                            'price' => $product1->price,
                        ],
                    ],
                ],
            ]);

        $product2 = Product::factory()->create();

        $response = $this->postJson(route('orders.add-product', ['order' => $order->id]), [
            'product_id' => $product2->id,
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => $order->is_paid,
                    'products' => [
                        [
                            'id' => $product1->id,
                            'product_name' => $product1->product_name,
                            'price' => $product1->price,
                        ],
                        [
                            'id' => $product2->id,
                            'product_name' => $product2->product_name,
                            'price' => $product2->price,
                        ],
                    ],
                ],
            ])->content();

        $totalPrice = $product1->price + $product2->price;
        $totalPriceInResponse = json_decode($response, false, 512, JSON_THROW_ON_ERROR)
            ->data
            ->payment_total;

        $this->assertEquals($totalPrice, $totalPriceInResponse);
    }

    /**
     * @throws \JsonException
     */
    public function test_pay_order_success(): void
    {
        $body = json_encode([
            'message' => 'Payment Successful',
        ], JSON_THROW_ON_ERROR);

        Http::fake([
            'https://superpay.view.agentur-loop.com/pay' => Http::response($body),
        ]);

        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => false,
        ]);

        $this->postJson(route('orders.pay', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => true,
                    'products' => [],
                ],
                'message' => 'Payment Successful',
            ]);
    }

    public function test_pay_order_insuf_funds(): void
    {
        $body = json_encode([
            'message' => 'Insufficient Funds',
        ], JSON_THROW_ON_ERROR);

        Http::fake([
            'https://superpay.view.agentur-loop.com/pay' => Http::response($body),
        ]);

        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => false,
        ]);

        $this->postJson(route('orders.pay', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => false,
                    'products' => [],
                ],
                'message' => 'Insufficient Funds',
            ]);
    }

    /**
     * @throws \JsonException
     */
    public function test_pay_fail(): void
    {
        $body = json_encode([
            'message' => 'Service Unavailable',
        ], JSON_THROW_ON_ERROR);

        Http::fake([
            'https://superpay.view.agentur-loop.com/pay' => Http::response($body, 503),
        ]);

        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => false,
        ]);

        $this->postJson(route('orders.pay', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => false,
                    'products' => [],
                ],
                'message' => 'Service Unavailable',
            ]);
    }

    /**
     * @throws \JsonException
     */
    public function test_pay_malformed(): void
    {
        $body = json_encode([
            'does_not_contain_msg' => 'Service Unavailable',
        ], JSON_THROW_ON_ERROR);

        Http::fake([
            'https://superpay.view.agentur-loop.com/pay' => Http::response($body),
        ]);

        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => false,
        ]);

        $this->postJson(route('orders.pay', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => false,
                    'products' => [],
                ],
                'message' => 'Service Unavailable',
            ]);
    }

    public function test_order_already_paid(): void
    {
        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'is_paid' => true,
        ]);

        $this->postJson(route('orders.pay', ['order' => $order->id]))
            ->assertStatus(200)
            ->assertJson([
                'order' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'is_paid' => true,
                    'products' => [],
                ],
                'message' => 'Order is already paid',
            ]);
    }

    public function test_attach_invalid_product(): void
    {
        $order = Order::factory()->create();

        $this->postJson(route('orders.add-product', ['order' => $order->id]), [
            'product_id' => 999,
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The selected product id is invalid.',
                'errors' => [
                    'product_id' => [
                        'The selected product id is invalid.',
                    ],
                ],
            ]);
    }
}
