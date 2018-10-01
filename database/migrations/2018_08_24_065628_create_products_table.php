<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('productName');
            $table->string('productDescription');
            $table->boolean('isUploaded');
            $table->boolean('isPublished');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('productId');
            $table->decimal('price',7,2);
            $table->decimal('tax',7,2);
            $table->decimal('taxRate',4,2);
            $table->integer('mediaId');
            $table->char('ean', 15)->unique();
            $table->timestamps();

            $table->foreign('productId')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');


        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('productId');
            $table->string('key');
            $table->string('value');

            $table->foreign('productId')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('views', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('productId');

            $table->foreign('productId')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('back', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->integer('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('left', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->integer('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('front', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->integer('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('right', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->integer('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('backCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('backId');
            $table->string('customization');

            $table->foreign('backId')
                    ->references('id')
                    ->on('back')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('leftCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('leftId');
            $table->string('customization');

            $table->foreign('leftId')
                    ->references('id')
                    ->on('left')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('frontCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('frontId');
            $table->string('customization');

            $table->foreign('frontId')
                    ->references('id')
                    ->on('front')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('rightCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rightId');
            $table->string('customization');

            $table->foreign('rightId')
                    ->references('id')
                    ->on('right')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rightCustomizations');
        Schema::dropIfExists('frontCustomizations');
        Schema::dropIfExists('leftCustomizations');
        Schema::dropIfExists('backCustomizations');
        Schema::dropIfExists('right');
        Schema::dropIfExists('front');
        Schema::dropIfExists('left');
        Schema::dropIfExists('back');
        Schema::dropIfExists('views');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('products');
    }
}
