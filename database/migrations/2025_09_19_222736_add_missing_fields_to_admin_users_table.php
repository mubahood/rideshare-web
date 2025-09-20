<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // Personal Information Fields
            if (!Schema::hasColumn('admin_users', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'phone_number_2')) {
                $table->string('phone_number_2')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'sex')) {
                $table->enum('sex', ['Male', 'Female'])->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'home_address')) {
                $table->text('home_address')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'current_address')) {
                $table->text('current_address')->nullable();
            }
            
            // Account Fields
            if (!Schema::hasColumn('admin_users', 'user_type')) {
                $table->enum('user_type', ['Admin', 'Driver', 'Pending Driver', 'Customer'])->default('Customer');
            }
            if (!Schema::hasColumn('admin_users', 'status')) {
                $table->enum('status', ['0', '1', '2'])->default('1')->comment('0=Blocked, 1=Active, 2=Pending');
            }
            if (!Schema::hasColumn('admin_users', 'ready_for_trip')) {
                $table->enum('ready_for_trip', ['Yes', 'No'])->default('No');
            }
            if (!Schema::hasColumn('admin_users', 'enterprise_id')) {
                $table->string('enterprise_id')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'otp')) {
                $table->string('otp')->nullable();
            }
            
            // Driver Documentation Fields
            if (!Schema::hasColumn('admin_users', 'nin')) {
                $table->string('nin')->nullable()->comment('National ID Number');
            }
            if (!Schema::hasColumn('admin_users', 'driving_license_number')) {
                $table->string('driving_license_number')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'driving_license_issue_date')) {
                $table->date('driving_license_issue_date')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'driving_license_validity')) {
                $table->date('driving_license_validity')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'driving_license_issue_authority')) {
                $table->string('driving_license_issue_authority')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'driving_license_photo')) {
                $table->string('driving_license_photo')->nullable();
            }
            if (!Schema::hasColumn('admin_users', 'automobile')) {
                $table->enum('automobile', ['car', 'motorcycle', 'truck', 'van', 'other'])->nullable();
            }
            
            // Additional Fields
            if (!Schema::hasColumn('admin_users', 'max_passengers')) {
                $table->integer('max_passengers')->default(4);
            }
            if (!Schema::hasColumn('admin_users', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0.00)->comment('User rating out of 5.00');
            }
            if (!Schema::hasColumn('admin_users', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->comment('Internal admin notes');
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
        Schema::table('admin_users', function (Blueprint $table) {
            $fieldsToRemove = [
                'first_name', 'last_name', 'phone_number', 'phone_number_2', 'email',
                'date_of_birth', 'place_of_birth', 'sex', 'home_address', 'current_address',
                'user_type', 'status', 'ready_for_trip', 'enterprise_id', 'otp',
                'nin', 'driving_license_number', 'driving_license_issue_date',
                'driving_license_validity', 'driving_license_issue_authority',
                'driving_license_photo', 'automobile', 'max_passengers', 'rating', 'admin_notes'
            ];
            
            foreach ($fieldsToRemove as $field) {
                if (Schema::hasColumn('admin_users', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
}
