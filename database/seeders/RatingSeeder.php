<?php

namespace Database\Seeders;

use App\Services\ReadLargeCSV;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatingSeeder extends Seeder
{
    public function run()
    {
        // Path to the ratings.csv file
        $file = 'resources/movies-dataset/ratings.csv';
        $csv_reader = new ReadLargeCSV($file, ",");

        $index = 0;
        $batchSize = 5000;  // Number of records to insert at a time
        $ratings = [];      // Array to hold records for batch insert

        foreach ($csv_reader->csvToArray() as $data) {
            foreach ($data as $lineValues) {
                // Skip the first row (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Extract user_id, movie_id, and rating
                $user_id = $lineValues[0];
                $movie_id = $lineValues[1];
                $rating = $lineValues[2];

                // Check if the movie exists in the movies table
                if (!DB::table('movies')->where('id', $movie_id)->exists()) {
                    continue;
                }

                // Add the rating record to the array for batch insert
                $ratings[] = [
                    'user_id' => $user_id,
                    'movie_id' => $movie_id,
                    'rating' => $rating,
                ];

                // Insert records in batches for performance
                if (count($ratings) >= $batchSize) {
                    DB::table('ratings')->insert($ratings);  // Batch insert
                    $ratings = [];  // Reset the array for the next batch
                }

                // Progress bar
                $this->displayProgress($index, 26024289);
            }
        }

        // Insert any remaining records
        if (!empty($ratings)) {
            DB::table('ratings')->insert($ratings);
        }

        echo "> 100% completed.\n";
    }

    /**
     * Display a progress bar.
     *
     * @param int $index
     * @param int $total
     * @return void
     */
    protected function displayProgress($index, $total)
    {
        $percentage = ($index / $total) * 100;

        static $actual = 0;

        if ($percentage - $actual >= 1) {  // Every 1% of completion
            echo "=";  // Print "=" to extend the loading bar
            $actual = $percentage;
        }

        static $completed = false;

        if ($percentage >= 99 && !$completed) {
            echo "> 100% completed.\n";
            $completed = true;  // Set completed to avoid triggering again
        }
    }
}
