<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActorMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_movie', function (Blueprint $table) {
            $table->bigIncrements('id');             // Auto-incrementing primary key (BIGINT)
            $table->unsignedInteger('actor_id');     // Foreign key for actor_id
            $table->unsignedBigInteger('movie_id');  // Foreign key for movie_id

            // Foreign key constraints
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');

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
        Schema::dropIfExists('actor_movie');         // Drop the correct table name
    }
}
