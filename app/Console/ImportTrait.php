<?php

namespace App\Console;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ImportTrait
{
    private function getCSVContent(): string
    {
        try {
            return Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get($this->config['url'])
                ->body();
        } catch (\Throwable $th) {
            $this->error('Could not fetch customers from remote service.');
            Log::error('Could not fetch customers from remote service.', [
                'exception' => $th,
            ]);
        }

        return '';
    }

    /**
     * @return array{success: array<int<0, max>, non-empty-array<string, string|null>>, fail: array<int<0, max>, string>}
     */
    private function parseCSVContent(string $csvContent): array
    {
        $lines = explode(PHP_EOL, $csvContent);
        $success = $fail = [];
        $headers = str_getcsv(array_shift($lines));

        Log::info('Parsing CSV content.', [
            'headers' => $headers,
        ]);

        foreach ($lines as $line) {
            try {
                $row = str_getcsv($line);

                if (count($row) === count($headers)) {
                    $parsedLine = array_combine($headers, $row);
                    $success[] = $parsedLine;

                    Log::info('Successfully parsed CSV lines.', [
                        'line' => $parsedLine,
                    ]);
                } else {
                    throw new \Exception('Number of columns does not match number of headers.');
                }
            } catch (\Throwable $th) {
                $fail[] = $line;
                Log::error('Could not parse CSV line.', [
                    'line' => $line,
                    'exception' => $th,
                ]);
            }
        }

        Log::info('Finished parsing CSV content.');
        Log::info('Successfully parsed CSV lines.', [
            'success' => count($success),
            'fail' => count($fail),
            'total' => count($lines),
        ]);

        return [
            'success' => $success,
            'fail' => $fail,
        ];
    }
}
