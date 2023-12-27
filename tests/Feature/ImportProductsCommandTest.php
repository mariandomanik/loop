<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_products(): void
    {
        $body = file_get_contents(base_path('tests/Fixtures/ImportCommands/products.csv'));

        Http::fake([
            config('loop_services.products.url') => Http::response($body),
        ]);

        $this->artisan('app:import-products')
            ->assertExitCode(0);

        $this->assertDatabaseCount('products', 100);
    }

    public function test_import_products_empty_response(): void
    {
        $body = '';

        Http::fake([
            config('loop_services.products.url') => Http::response($body),
        ]);

        $this->artisan('app:import-products')
            ->assertExitCode(1);

        $this->assertDatabaseCount('products', 0);
    }
}
