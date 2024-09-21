<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->bigIncrements('id');            // Auto-incrementing BIGINT primary key
            $table->float('vote_average');          // FLOAT column for average vote
            $table->integer('vote_count');          // INT column for vote count
            $table->unsignedBigInteger('movie_id'); // Foreign key for movie_id

            // Defining the foreign key constraint
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');

            $table->timestamps();                  // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('votes');
    }
}
