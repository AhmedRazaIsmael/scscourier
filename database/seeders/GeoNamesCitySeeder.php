<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class GeoNamesCitySeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/allCountries.txt');

        if (!file_exists($filePath)) {
            echo "❌ File not found: $filePath\n";
            return;
        }

        $handle = fopen($filePath, 'r');
        $batch = [];
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $parts = explode("\t", trim($line));

            if (count($parts) < 9) continue;

            $name = $parts[1] ?? null;
            $countryCode = $parts[8] ?? null;
            $stateCode = $parts[10] ?? null;
            $latitude = $parts[4] ?? null;
            $longitude = $parts[5] ?? null;
            $population = $parts[14] ?? null;

            if (!$countryCode || !$name) continue;

            $batch[] = [
                'code' => 'CITY-' . uniqid(),
                'name' => $name,
                'country_code' => $countryCode,
                'state_code' => $stateCode,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'population' => $population,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 1000
            if (count($batch) >= 1000) {
                City::insert($batch);
                $count += count($batch);
                $batch = [];
                echo "Inserted $count cities...\n";
            }
        }

        if (count($batch)) {
            City::insert($batch);
            $count += count($batch);
        }

        fclose($handle);
        echo "✅ Import complete! Total: $count cities inserted.\n";
    }
}
