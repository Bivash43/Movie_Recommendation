<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_movie', function (Blueprint $table) {
            $table->bigIncrements('id');             // Auto-incrementing primary key
            $table->unsignedInteger('company_id');   // Foreign key for company_id
            $table->unsignedBigInteger('movie_id');  // Foreign key for movie_id

            // Defining foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
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
        Schema::dropIfExists('company_movie');
    }
}
