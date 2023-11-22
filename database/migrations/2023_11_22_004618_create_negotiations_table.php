<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNegotiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('customer_id')->nullable();
            $table->text('customer_name')->nullable();
            $table->integer('driver_id')->nullable();
            $table->text('driver_name')->nullable();
            $table->string('status')->default('Pending');
            $table->string('customer_accepted')->default('Pending');
            $table->string('customer_driver')->default('Pending');
            $table->text('pickup_lat')->nullable();
            $table->text('pickup_lng')->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('dropoff_lat')->nullable();
            $table->text('dropoff_lng')->nullable();
            $table->text('dropoff_address')->nullable();
            $table->text('records')->nullable();
            $table->text('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('negotiations');
    }
}
