<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenreMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genre_movie', function (Blueprint $table) {
            $table->bigIncrements('id');            // Auto-incrementing primary key
            $table->unsignedInteger('genre_id');    // Foreign key for genre
            $table->unsignedBigInteger('movie_id'); // Foreign key for movie

            // Foreign key constraints
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');

            $table->timestamps();                   // Adds created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('genre_movie');
    }
}
