<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotsColumnToTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('trips', 'slots')) {
                $table->integer('slots')->default(1)->after('status')->comment('Number of available seats/slots');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'slots')) {
                $table->dropColumn('slots');
            }
        });
    }
}
