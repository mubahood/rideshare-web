<?php

use App\Models\RouteStage;
use App\Models\Trip;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Trip::class, 'trip_id');
            $table->foreignIdFor(Administrator::class, 'customer_id');
            $table->foreignIdFor(Administrator::class, 'driver_id');
            $table->foreignIdFor(RouteStage::class, 'start_stage_id');
            $table->foreignIdFor(RouteStage::class, 'end_stage_id');
            $table->string('status')->default('Pending');
            $table->string('payment_status')->default('Pending');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->integer('slot_count')->nullable()->default(1);
            $table->integer('price')->nullable()->default(1);
            $table->text('customer_note')->nullable();
            $table->text('start_stage_text')->nullable();
            $table->text('end_stage_text')->nullable();
            $table->text('trip_text')->nullable();
            $table->text('customer_text')->nullable();
            $table->text('driver_text')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_bookings');
    }
}
