<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportCustomersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_customers(): void
    {
        $body = file_get_contents(base_path('tests/Fixtures/ImportCommands/customers.csv'));

        Http::fake([
            config('loop_services.customers.url') => Http::response($body),
        ]);

        $this->artisan('app:import-customers')
            ->assertExitCode(0);

        $this->assertDatabaseCount('customers', 1993);
        $this->assertDatabaseHas('customers', [
            'job_title' => 'Operator',
            'email' => 'Chuck_Walsh1748@supunk.biz',
            'phone' => '6-768-183-6768',
            'firstname_lastname' => 'Chuck Walsh',
            'registered_since' => '2019-07-20T00:00:00+00:00',
        ]);

        $this->assertDatabaseHas('customers', [
            'job_title' => 'Laboratory Technician',
            'email' => 'Chad_Wood145@grannar.com',
            'phone' => '0-740-848-4367',
            'firstname_lastname' => 'Chad Wood',
            'registered_since' => '2019-03-17T00:00:00+00:00',
        ]);

        $this->assertDatabaseMissing('customers', [
            'firstname_lastname' => 'CustomerWith WrongEmail',
        ]);
    }

    public function test_import_customers_empty_response(): void
    {
        $body = '';

        Http::fake([
            config('loop_services.customers.url') => Http::response($body),
        ]);

        $this->artisan('app:import-customers')
            ->assertExitCode(1);

        $this->assertDatabaseCount('customers', 0);
    }
}
