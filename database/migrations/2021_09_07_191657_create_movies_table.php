<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->bigIncrements('id');  // Auto-incrementing primary key (BIGINT)
            $table->text('title');        // Text field for the movie title
            $table->text('overview');     // Text field for the movie overview
            $table->date('release_date')->nullable(); // Nullable date field for release date
            $table->unsignedBigInteger('language_id')->nullable(); // Foreign key to languages table

            // Defining the foreign key constraint
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            $table->timestamps();         // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}
