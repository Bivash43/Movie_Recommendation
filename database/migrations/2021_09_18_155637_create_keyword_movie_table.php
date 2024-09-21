<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyword_movie', function (Blueprint $table) {
            $table->bigIncrements('id');             // Auto-incrementing primary key (BIGINT)
            $table->unsignedBigInteger('movie_id');  // Foreign key for movie_id
            $table->unsignedInteger('keyword_id');   // Foreign key for keyword_id

            // Foreign key constraints
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');

            $table->timestamps();                   // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keyword_movie');
    }
}
