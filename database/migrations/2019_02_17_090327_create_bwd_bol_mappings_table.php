<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBwdBolMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bwd_bol_mappings', function (Blueprint $table) {
            $table->increments('mapping_id');
            $table->string('smakeVariantId');
            $table->string('ean');
            $table->string('id');
            $table->string('filename');
            $table->string('size');
            $table->string('variantName');
            $table->string('productgroup');
            $table->string('materialdescription');
            $table->string('gender');
            $table->string('seasonalyear');
            $table->string('targetaudience');
            $table->string('salespitch');
            $table->string('brand');
            $table->string('material');
            $table->string('sleevelength');
            $table->string('shirttype');
            $table->string('seasonalcollection');
            $table->string('instructions');
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
        Schema::dropIfExists('bwd_bol_mappings');
    }
}
