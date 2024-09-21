<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    public function run()
    {
        // Get info from file movies_metadata.csv
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into images table: ";

            $index = 0; // Count iterations for progress tracking

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Check if movie ID exists and row has a valid length
                if (empty($lineValues[5]) || count($lineValues) < 20) {
                    continue;
                }

                // Check if the movie exists in the movies table
                $movie = Movie::find($lineValues[5]);
                if (!$movie) {
                    continue;
                }

                // If images object doesn't exist, skip to the next iteration
                if (empty($lineValues[1])) {
                    continue;
                }

                // Decode the JSON string for images
                $imageObject = json_decode(str_replace("'", "\"", $lineValues[1]));

                $poster_path = $imageObject->poster_path ?? null;
                $backdrop_path = $imageObject->backdrop_path ?? null;

                // If both poster and backdrop are missing, skip to the next iteration
                if (!$poster_path && !$backdrop_path) {
                    continue;
                }

                // Insert image into the images table
                Image::create([
                    'poster_path' => $poster_path,
                    'backdrop_path' => $backdrop_path,
                    'movie_id' => $movie->id,
                ]);

                // Progress bar display (every 10% increment)
                $percentage = ($index / 45575) * 100;
                if ($percentage % 10 == 0) {
                    echo "=";
                }
                if ($percentage >= 100) {
                    echo "> 100% completed.\n";
                }

                $index++;
            }

            fclose($handle);
        }
    }
}
