<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNegotiationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('negotiation_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('negotiation_id');
            $table->integer('customer_id');
            $table->integer('driver_id');
            $table->integer('last_negotiator_id');
            $table->integer('first_negotiator_id');
            $table->string('price_accepted')->define('No');
            $table->integer('price');
            $table->string('message_type')->define('Negotiation');
            $table->text('message_body')->nullable();
            $table->text('image_url')->nullable();
            $table->text('audio_url')->nullable();
            $table->string('is_received')->define('No');
            $table->string('is_seen')->define('No');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('negotiation_records');
    }
}
