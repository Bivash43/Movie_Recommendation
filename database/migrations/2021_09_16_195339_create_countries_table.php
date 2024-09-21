<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');                // Auto-incrementing primary key (INT)
            $table->string('short', 2);              // VARCHAR(2) for country short code
            $table->string('country_name', 50);      // VARCHAR(50) for country name

            $table->timestamps();                    // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();      // Disable foreign key constraints before dropping
        Schema::dropIfExists('countries');           // Drop the countries table
        Schema::enableForeignKeyConstraints();       // Re-enable foreign key constraints
    }
}
