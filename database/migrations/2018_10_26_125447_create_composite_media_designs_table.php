<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompositeMediaDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('composite_media_designs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('designName')->Unique();
            $table->string('baseColor');
            $table->integer('designId');
            $table->integer('smakeId')->nullable();
            $table->string('fileName');
            $table->string('fileFolder');
            $table->integer('fileSize')->nullable();
            $table->string('smakeFileName')->nullable();
            $table->string('smakeDownloadUrl')->nullable();
            $table->double('width_px',5,0);
            $table->double('height_px',5,0);
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
        Schema::dropIfExists('composite_media_designs');
    }
}
