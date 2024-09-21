<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Language;
use Illuminate\Database\Seeder;

class MoviesSeeder extends Seeder
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
            echo "Inserting data into movies table: ";

            $index = 0;

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Check if the movie already exists or if the row has an invalid length
                if (Movie::find($lineValues[5]) || count($lineValues) < 20) {
                    continue;
                }

                // Handle language: get the language ID or set it to null if invalid
                $language = Language::where('short', $lineValues[7])->first();
                $languageId = $language ? $language->id : null;

                // Insert the movie using Eloquent
                Movie::create([
                    'id' => $lineValues[5], // Movie ID
                    'title' => $lineValues[20] ?? '', // Title
                    'overview' => $lineValues[9] ?? '', // Overview
                    'release_date' => $lineValues[14] ?: null, // Release date
                    'language_id' => $languageId, // Language ID
                ]);

                // Display progress
                $this->displayProgress($index, 45575);
            }

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

        static $actual = 0; // Save the actual percentage of completion

        if ($percentage - $actual >= 10) { // Every 10% of completion
            echo "="; // Print "=" to extend the loading bar
            $actual = $percentage; // Update the actual percentage
        }

        static $completed = false;

        if ($percentage >= 99 && !$completed) {
            echo "> 100% completed.\n";
            $completed = true; // Set completed to avoid triggering the final message again
        }
    }
}
