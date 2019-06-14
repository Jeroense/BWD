<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOfferDataUploadedToBolAddProcesStatusIdAddAddEventType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_data_uploaded_to_bol', function(Blueprint $table){

            $table->string('process_status_id')->after('id');
            $table->string('eventType')->after('process_status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_data_uploaded_to_bol', function(Blueprint $table){

        $table->dropColumn('process_status_id');
        $table->dropColumn('eventType');
        });

    }
}
