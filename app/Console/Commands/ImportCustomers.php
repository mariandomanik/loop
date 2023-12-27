<?php

namespace App\Console\Commands;

use App\Console\ImportTrait;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportCustomers extends Command
{
    use ImportTrait;

    /**
     * @var array{url: string, username: string, password: string}
     */
    private array $config;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import customers from a remote CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->config = config('loop_services.customers');
        Log::info('Downloading customers from remote service.');

        $content = $this->getCSVContent();

        if ($content === '') {
            $this->error('Could not download customers. Exiting.');

            return 1;
        }

        Log::info('Successfully downloaded customers.');
        $customers = $this->parseCSVContent($content);
        $created = $failed = 0;

        foreach ($customers['success'] as $customer) {
            try {
                $this->info('Creating customer: '.$customer['ID']);
                Log::info('Creating customer.', [
                    'customer' => $customer,
                ]);

                $customer['registered_since'] = $this->parseDate($customer['registered_since']);

                if ($customer['registered_since'] === ''
                        || ! $this->validateEmail($customer['Email Address'])) {
                    throw new \Exception('Invalid data.');
                }

                Customer::create([
                    'job_title' => $customer['Job Title'],
                    'email' => $customer['Email Address'],
                    'phone' => $customer['phone'],
                    'firstname_lastname' => $customer['FirstName LastName'],
                    'registered_since' => $customer['registered_since'],
                ]);
                $created++;
            } catch (\Throwable $th) {
                $this->error('Could not create customer: '.$customer['ID']);
                Log::error('Could not create customer.', [
                    'customer' => $customer,
                    'exception' => $th,
                ]);
                $failed++;
            }
        }

        $failed = count($customers['fail']) + $failed;

        $this->info('Finished importing customers. Created '.$created.' customers, failed to import '.$failed.' customers.');
        Log::info('Finished importing customers.', [
            'created_count' => $created,
            'failed_count' => $failed,
        ]);

        return 0;
    }

    private function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function parseDate(string $date): string
    {
        try {
            //Remove the day of the week from the date, e.g. "Monday, January 1, 2021" => "January 1, 2021"
            $parsedDate = preg_replace('/^.*?,/', '', $date);

            return Carbon::createFromFormat('F j,Y', $parsedDate)->startOfDay()->toIso8601String();
        } catch (\Throwable $th) {
            Log::error('Could not parse date.', [
                'date' => $date,
                'exception' => $th,
            ]);
        }

        return '';
    }
}
