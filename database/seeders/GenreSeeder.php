<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    public function run()
    {
        // Get info from file movies_metadata.csv
        $filePath = base_path("resources/movies-dataset/movies_metadata.csv");
        $handle = fopen($filePath, "r");

        if ($handle) {
            echo "Inserting data into genres table: ";

            $index = 0; // Count iterations for progress tracking

            while (($lineValues = fgetcsv($handle, 0, ",")) !== false) {
                // Skip the first line (headers)
                if ($index++ == 0) {
                    continue;
                }

                // Skip if the genre column doesn't exist or is invalid
                if (empty($lineValues[3])) {
                    continue;
                }

                $genreObjects = json_decode(str_replace("'", "\"", $lineValues[3]));

                // Check if the movie ID exists and the row has a valid length
                if (empty($lineValues[5]) || count($lineValues) < 20) {
                    continue;
                }

                // Check if the movie exists in the movies table
                $movie = Movie::find($lineValues[5]);
                if (!$movie) {
                    continue;
                }

                // Handle genres
                foreach ($genreObjects as $genreObject) {
                    $genre_id = $genreObject->id ?? null;
                    $genre_name = $genreObject->name ?? null;

                    // Skip if genre ID or name is missing
                    if (!$genre_id || !$genre_name) {
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

                    // Insert or find the genre in the database
                    $genre = Genre::firstOrCreate(
                        ['id' => $genre_id],
                        ['genre' => $genre_name]
                    );

                    // Attach the genre to the movie if the relationship doesn't exist
                    if (!$genre->getMovies()->where('movie_id', $movie->id)->exists()) {
                        $genre->getMovies()->attach($movie->id);
                    }
                }
            }

            fclose($handle);
        }
    }
}
