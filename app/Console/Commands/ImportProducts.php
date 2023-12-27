<?php

namespace App\Console\Commands;

use App\Console\ImportTrait;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportProducts extends Command
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
    protected $signature = 'app:import-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a remote CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->config = config('loop_services.products');
        Log::info('Downloading products from remote service.');

        $content = $this->getCSVContent();

        if ($content === '') {
            $this->error('Could not download products. Exiting.');

            return 1;
        }

        Log::info('Successfully downloaded products.');
        $products = $this->parseCSVContent($content);
        $created = $failed = 0;

        foreach ($products['success'] as $product) {
            try {
                $this->info('Creating product: '.$product['productname']);
                Log::info('Creating product '.$product['productname']);
                Product::create([
                    'product_name' => $product['productname'],
                    'price' => $product['price'],
                ]);
                $created++;
            } catch (\Throwable $th) {
                $this->error('Could not create product: '.$product['productname']);
                Log::error('Could not create product.', [
                    'product' => $product,
                    'exception' => $th,
                ]);

                $failed++;

                continue;
            }
        }

        $failed = count($products['fail']) + $failed;

        $this->info('Finished importing products. Created '.$created.' products, failed to import '.$failed.' products.');
        Log::info('Finished importing products.', [
            'created_count' => $created,
            'failed_count' => $failed,
        ]);

        return 0;
    }
}
