<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createCustomers(int $count = 1): void
    {
        Customer::factory()->count($count);
    }

    protected function createProduct(int $count = 1): void
    {
        Product::factory()->count($count);
    }

    protected function createOrder(int $count = 1): Collection
    {
        return Order::factory()->count($count)->make();
    }
}
