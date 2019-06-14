<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProcesStatusIdToBigIntOnOfferDataUploadedToBol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_data_uploaded_to_bol', function(Blueprint $table){

            $table->unsignedBigInteger('process_status_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_data_uploaded_to_bol', function(Blueprint $table)
        {
            $table->string('process_status_id')->change();
        });

    }
}
