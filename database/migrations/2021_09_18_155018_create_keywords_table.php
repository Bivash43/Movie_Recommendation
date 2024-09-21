<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->increments('id');             // Auto-incrementing INT primary key
            $table->string('keyword', 50);        // VARCHAR(50) for the keyword

            $table->timestamps();                 // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();   // Disable foreign key constraints before dropping
        Schema::dropIfExists('keywords');         // Drop the keywords table
        Schema::enableForeignKeyConstraints();    // Re-enable foreign key constraints
    }
}
