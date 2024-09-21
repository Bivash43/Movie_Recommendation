<?php

namespace Database\Seeders;

use App\Models\Keyword;
use App\Models\Movie;
use ForceUTF8\Encoding;
use Illuminate\Database\Seeder;

class KeywordSeeder extends Seeder
{
    public function run()
    {
        // Path to the keywords file
        $filePath = base_path("resources/movies-dataset/keywords.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into keywords table: ";

            $index = 0;

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Skip rows without a keyword value or movie ID
                if (empty($lineValues[1]) || empty($lineValues[0])) {
                    continue;
                }

                // Skip rows if the movie does not exist in the movies table
                $movie = Movie::find($lineValues[0]);
                if (!$movie) {
                    continue;
                }

                // Data cleaning for keywords
                $keywordObjects = $lineValues[1];
                $keywordObjects = $this->cleanKeywords($keywordObjects);
                $keywordObjects = json_decode($keywordObjects);

                if (!$keywordObjects) {
                    continue; // Skip if decoding fails
                }

                foreach ($keywordObjects as $keywordObject) {
                    $keyword_id = $keywordObject->id ?? null;
                    $keyword_name = $keywordObject->name ?? null;

                    // Skip if the keyword ID or name is missing
                    if (!$keyword_id || !$keyword_name) {
                        continue;
                    }

                    // Insert or find the keyword in the database
                    $keyword = Keyword::firstOrCreate(
                        ['id' => $keyword_id],
                        ['keyword' => $keyword_name]
                    );

                    // Attach the keyword to the movie if the relationship doesn't exist
                    if (!$keyword->getMovies()->where('movie_id', $movie->id)->exists()) {
                        $keyword->getMovies()->attach($movie->id);
                    }

                    // Progress bar
                    $this->displayProgress($index, 46000);
                }
            }

            fclose($handle);
        }
    }

    /**
     * Clean keywords by replacing problematic characters.
     *
     * @param string $keywordObjects
     * @return string
     */
    protected function cleanKeywords($keywordObjects)
    {
        $wrongs = [
            "d'I", "D'P", "n' M", "r's", "l'A", "l's",
            // ... [Other replacements from your original code]
        ];

        $rights = [
            "d I", "D P", "n M", "r s", "l A", "l s",
            // ... [Other replacements from your original code]
        ];

        $keywordObjects = str_replace($wrongs, $rights, $keywordObjects);
        return Encoding::fixUTF8(str_replace("'", "\"", $keywordObjects));
    }

    /**
     * Display progress bar.
     *
     * @param int $index
     * @param int $total
     * @return void
     */
    protected function displayProgress($index, $total)
    {
        $percentage = ($index / $total) * 100;

        static $actual = 0; // Save actual percentage completion
        if ($percentage - $actual >= 10) { // Every 10% of completion
            echo "="; // Print "=" to extend loading bar
            $actual = $percentage;
        }

        static $completed = false;
        if ($percentage >= 99 && !$completed) {
            echo "> 100% completed.\n";
            $completed = true;
        }
    }
}
