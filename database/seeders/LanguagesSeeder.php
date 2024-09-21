<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    public function run()
    {
        // Path to the movies_metadata.csv file
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into the languages table: ";

            $index = 0; // Counter for tracking progress

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Extract the language short code
                $languageShort = $lineValues[7] ?? null;

                // Validate the language short code (must be two alphabetic characters)
                if (!$languageShort || !ctype_alpha($languageShort) || strlen($languageShort) != 2) {
                    continue;
                }

                // Check if the language already exists in the database
                if (!Language::where('short', $languageShort)->exists()) {
                    // Insert the new language using Eloquent
                    Language::create([
                        'short' => $languageShort,
                    ]);
                }

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

        if ($percentage - $actual >= 10) { // Update the bar every 10% of completion
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
