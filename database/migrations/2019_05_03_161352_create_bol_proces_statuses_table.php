<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBolProcesStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bol_proces_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('process_status_id');
            $table->string('entityId')->nullable();
            $table->string('eventType');
            $table->string('description')->nullable();
            $table->string('status');
            $table->string('errorMessage')->nullable();
            $table->boolean('csv_success')->nullable()->default(false);  // true als er een succesvolle cvs-offer-export is opgehaald vanuit deze db-entry
            $table->string('link_to_self');
            $table->string('method_to_self');
            $table->string('createTimestamp'); // geeft timestamp aan wanneer 1e opdracht to aanmaak van eventtype gegeven is.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bol_proces_statuses');
    }
}
