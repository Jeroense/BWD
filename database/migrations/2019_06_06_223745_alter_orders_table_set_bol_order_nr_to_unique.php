<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTableSetBolOrderNrToUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){

            $table->string('bolOrderNr')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function(Blueprint $table){

            $table->string('bolOrderNr')->nullable()->change();
    });
    }
}

