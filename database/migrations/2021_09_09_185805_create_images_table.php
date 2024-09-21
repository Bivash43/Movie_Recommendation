<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');             // Auto-incrementing primary key (BIGINT)
            $table->string('poster_path', 40)->nullable();  // VARCHAR(40), nullable
            $table->string('backdrop_path', 40)->nullable(); // VARCHAR(40), nullable
            $table->unsignedBigInteger('movie_id');  // Foreign key referencing the movies table

            // Defining the foreign key constraint
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
        Schema::dropIfExists('images');
    }
}
