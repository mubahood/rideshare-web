<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeedModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seed_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable()->comment('Name of the seed');
            $table->text('description')->nullable()->comment('Description of the seed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seed_models');
    }
}
