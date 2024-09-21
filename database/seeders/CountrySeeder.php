<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run()
    {
        // Get info from the file movies_metadata.csv
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into countries table: ";

            $index = 0; // Count iterations for progress tracking

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Skip rows where the country column doesn't exist or is invalid
                if (empty($lineValues[13]) || $lineValues[13] == "[]") {
                    continue;
                }

                $countryObjects = $lineValues[13]; // Country column value

                // Check if movie ID exists and row has a valid length
                if (empty($lineValues[5]) || count($lineValues) < 20) {
                    continue;
                }

                // Check if the movie exists in the movies table
                if (!Movie::where('id', $lineValues[5])->exists()) {
                    continue;
                }

                // Replace problematic characters and decode the country JSON
                $wrongs = ["D'I", "e's"];
                $rights = ["D I", "e s"];
                $countryObjects = str_replace($wrongs, $rights, $countryObjects);
                $countryObjects = json_decode(str_replace("'", "\"", $countryObjects));

                // If JSON decode fails, skip
                if (!$countryObjects) {
                    continue;
                }

                foreach ($countryObjects as $countryObject) {
                    $country_short = $countryObject->iso_3166_1 ?? null;
                    $country_name = $countryObject->name ?? null;

                    // Skip if country short code or name is missing
                    if (!$country_short || !$country_name) {
                        continue;
                    }

                    // Display progress (every 10% increment)
                    $percentage = ($index / 181000) * 100;
                    if ($percentage % 10 == 0) {
                        echo "=";
                    }
                    if ($percentage >= 100) {
                        echo "> 100% completed.\n";
                    }

                    // Check if country already exists, if not, create it
                    $country = Country::firstOrCreate(
                        ['short' => $country_short],
                        ['country_name' => $country_name]
                    );

                    // Associate movie with country if the relationship doesn't already exist
                    if (!$country->getMovies()->where('movie_id', $lineValues[5])->exists()) {
                        $country->getMovies()->attach($lineValues[5]);
                    }

                    $index++;
                }
            }

            fclose($handle);
        }
    }
}
