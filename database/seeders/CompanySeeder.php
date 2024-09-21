<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use \ForceUTF8\Encoding;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Get info from file movies_metadata.csv
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into companies table: ";

            $index = 0; // count iterations for progress tracking

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    echo "Skipping header\n";
                    continue;
                }

                // Skip lines where the company column is empty or not valid
                if (empty($lineValues[12]) || $lineValues[12] == "[]") {
                    continue;
                }

                $companyObjects = $lineValues[12]; // Save the company column value

                // Check if movie ID exists and the row has a valid length
                if (empty($lineValues[5]) || sizeof($lineValues) < 20) {
                    continue;
                }

                // Check if the movie exists in the movies table
                if (!Movie::where('id', $lineValues[5])->exists()) {
                    continue;
                }

                // Data cleaning
                $companyObjects = preg_replace('/[\x00-\x1F\x7F]/', '', $companyObjects);
                $companyObjects = Encoding::fixUTF8($companyObjects);
                $companyObjects = json_decode($companyObjects, JSON_INVALID_UTF8_IGNORE);

                if (!$companyObjects) {
                    continue; // If json decoding fails, skip
                }

                foreach ($companyObjects as $companyObject) {
                    $company_id = $companyObject['id'] ?? null;
                    $company_name = $companyObject['name'] ?? null;

                    // Skip if the company ID or name is missing
                    if (!$company_id || !$company_name) {
                        continue;
                    }

                    // Show progress (every 10%)
                    $percentage = ($index / 82000) * 100;
                    if ($percentage % 10 == 0) {
                        echo "=";
                    }
                    if ($percentage >= 100) {
                        echo "> 100% completed.\n";
                    }

                    // If the company already exists, attach the relationship and skip to the next
                    $company = Company::firstOrCreate(['id' => $company_id], ['company' => $company_name]);
                    $movie = Movie::find($lineValues[5]);

                    if (!$company->getMovies()->where('movie_id', $movie->id)->exists()) {
                        $company->getMovies()->attach($movie->id);
                    }
                }
            }

            fclose($handle);
        }
    }
}
