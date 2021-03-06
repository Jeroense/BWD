<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Database\seeds;

class CreateTshirtmetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tshirtmetrics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('size');
            $table->string('length_mm');

            $table->index('size');
        });

        $seeder = new tshirtMetricsSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tshirtmetrics');
    }
}
