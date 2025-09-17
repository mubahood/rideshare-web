<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // Service capability fields
            $table->string('is_car')->nullable()->default('No');
            $table->string('is_boda')->nullable()->default('No');
            $table->string('is_ambulance')->nullable()->default('No');
            $table->string('is_police')->nullable()->default('No');
            $table->string('is_delivery')->nullable()->default('No');
            $table->string('is_breakdown')->nullable()->default('No');
            $table->string('is_firebrugade')->nullable()->default('No');
            
            // Service approval fields
            $table->string('is_car_approved')->nullable()->default('No');
            $table->string('is_boda_approved')->nullable()->default('No');
            $table->string('is_ambulance_approved')->nullable()->default('No');
            $table->string('is_police_approved')->nullable()->default('No');
            $table->string('is_delivery_approved')->nullable()->default('No');
            $table->string('is_breakdown_approved')->nullable()->default('No');
            $table->string('is_firebrugade_approved')->nullable()->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // Drop service capability fields
            $table->dropColumn([
                'is_car',
                'is_boda', 
                'is_ambulance',
                'is_police',
                'is_delivery',
                'is_breakdown',
                'is_firebrugade',
                
                // Drop service approval fields
                'is_car_approved',
                'is_boda_approved',
                'is_ambulance_approved', 
                'is_police_approved',
                'is_delivery_approved',
                'is_breakdown_approved',
                'is_firebrugade_approved'
            ]);
        });
    }
};