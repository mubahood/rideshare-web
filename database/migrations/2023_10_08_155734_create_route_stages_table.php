<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_stages', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable()->comment('Name of the stage');
            $table->string('latitute')->nullable()->default('')->comment('Latitute of the stage');
            $table->string('longitude')->nullable()->default('')->comment('Longitude of the stage');
            $table->text('details')->nullable()->comment('Address of the stage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('route_stages');
    }
}
