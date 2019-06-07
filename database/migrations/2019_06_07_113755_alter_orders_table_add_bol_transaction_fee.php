<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTableAddBolTransactionFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){

            $table->double('boltransactionFee', 8,2)->after('orderAmount')->nullable();
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

        $table->dropColumn('boltransactionFee');

    });
    }
}
