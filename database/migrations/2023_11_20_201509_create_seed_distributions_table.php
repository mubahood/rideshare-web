<?php

use App\Models\SeedModel;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeedDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seed_distributions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'farmer_id');
            $table->foreignIdFor(SeedModel::class, 'seed_id');
            $table->text('quantity')->nullable()->comment('Quantity of the seed');
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
        Schema::dropIfExists('seed_distributions');
    }
}
