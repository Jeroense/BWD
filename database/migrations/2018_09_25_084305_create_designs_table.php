<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('smakeId')->nullable();
            $table->string('smakeFileName', 150)->nullable();
            $table->string('fileName', 100);
            $table->string('originalName', 150);
            $table->string('mimeType', 100);
            $table->string('fileSize', 50);
            $table->string('path', 190);
            $table->string('downloadUrl', 190)->nullable();
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
        Schema::dropIfExists('designs');
    }
}
