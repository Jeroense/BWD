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
            $table->string('smakeVariantId', 100);
            $table->string('ean', 15);
            $table->string('id', 100);
            $table->string('filename', 100);
            $table->string('size', 25);
            $table->string('variantName', 190);
            $table->string('productgroup', 100);
            $table->string('materialdescription', 190);
            $table->string('gender', 25);
            $table->string('seasonalyear', 100);
            $table->string('targetaudience', 100);
            $table->mediumText('salespitch');
            $table->string('brand', 190);
            $table->string('material', 190);
            $table->string('sleevelength', 100);
            $table->string('shirttype', 100);
            $table->string('seasonalcollection', 100);
            $table->string('instructions', 190);
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
