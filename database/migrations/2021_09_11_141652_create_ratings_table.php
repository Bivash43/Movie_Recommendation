<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('id');            // Auto-incrementing BIGINT primary key
            $table->unsignedInteger('user_id');     // INT field for user_id
            $table->unsignedBigInteger('movie_id'); // Foreign key for movie_id
            $table->float('rating');                // FLOAT field for the rating

            // Foreign key constraint for movie_id
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
        Schema::dropIfExists('ratings');
    }
}
