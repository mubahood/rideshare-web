<?php

use App\Models\RouteStage;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'driver_id')->nullable();
            $table->foreignIdFor(Administrator::class, 'customer_id')->nullable();
            $table->foreignIdFor(RouteStage::class, 'start_stage_id')->nullable();
            $table->foreignIdFor(RouteStage::class, 'end_stage_id')->nullable();
            $table->string('scheduled_start_time')->nullable()->comment('Start time of the trip')->nullable();
            $table->string('scheduled_end_time')->nullable()->comment('End time of the trip')->nullable();
            $table->string('start_time')->nullable()->comment('Start time of the trip')->nullable();
            $table->string('end_time')->nullable()->comment('End time of the trip')->nullable();
            $table->string('status')->nullable()->comment('Status of the trip')->nullable();
            $table->string('vehicel_reg_number')->nullable()->comment('Status of the trip')->nullable();
            $table->integer('slots')->nullable()->comment('Number of slots')->nullable();
            $table->text('details')->nullable()->comment('Details of the trip')->nullable();
            $table->text('car_model')->nullable()->comment('Details of the trip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
