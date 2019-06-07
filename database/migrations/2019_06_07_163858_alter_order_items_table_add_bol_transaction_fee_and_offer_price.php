<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrderItemsTableAddBolTransactionFeeAndOfferPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderitems', function(Blueprint $table){

            $table->double('bolTotalOfferPrice', 8,2)->after('qty')->nullable();    // het produkt van qty en single offer price volgens bol
            $table->double('bolTransactionFee', 8,2)->after('bolTotalOfferPrice')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orderitems', function(Blueprint $table){

            $table->dropColumn('bolTotalOfferPrice');
            $table->dropColumn('bolTransactionFee');
        });
    }
}
