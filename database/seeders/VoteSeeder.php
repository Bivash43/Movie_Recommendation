<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to the movies_metadata.csv file
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into votes table: ";

            $index = 0;
            $batchSize = 5000;  // Number of records to insert at a time
            $votes = [];        // Array to hold records for batch insert

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first row (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Check if the movie ID and the row length are valid
                if (empty($lineValues[5]) || count($lineValues) < 20) {
                    continue;
                }

                // Check if the movie exists in the movies table
                if (!DB::table('movies')->where('id', $lineValues[5])->exists()) {
                    continue;
                }

                // Extract vote average and count
                $vote_average = $lineValues[22] ?? null;
                $vote_count = $lineValues[23] ?? null;

                // Skip if vote average or count are not numeric
                if (!is_numeric($vote_average) || !is_numeric($vote_count)) {
                    continue;
                }

                // Add the vote record to the array for batch insert
                $votes[] = [
                    'vote_average' => $vote_average,
                    'vote_count' => $vote_count,
                    'movie_id' => $lineValues[5], // Movie ID
                ];

                // Insert records in batches for performance
                if (count($votes) >= $batchSize) {
                    DB::table('votes')->insert($votes);  // Batch insert
                    $votes = [];  // Reset the array for the next batch
                }

                // Display progress
                $this->displayProgress($index, 45575);
            }

            // Insert any remaining records
            if (!empty($votes)) {
                DB::table('votes')->insert($votes);
            }

            echo "> 100% completed.\n";

            fclose($handle);
        }
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

        if ($percentage - $actual >= 10) {  // Every 10% of completion
            echo "=";  // Print "=" to extend the loading bar
            $actual = $percentage;
        }

        static $completed = false;

        if ($percentage >= 99 && !$completed) {
            echo "> 100% completed.\n";
            $completed = true;  // Avoid triggering completion message again
        }
    }
}
