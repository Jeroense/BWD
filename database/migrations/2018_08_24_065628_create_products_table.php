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
            $table->string('smakeId');
            $table->string('productName');
            $table->string('productDescription');
            $table->timestamps();
        });

        Schema::create('variants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('productId')->nullable();
            $table->unsignedInteger('variantId')->unique()->nullable();
            $table->unsignedInteger('parentVariantId')->nullable();
            $table->boolean('isBwdVariant')->nullable();
            $table->decimal('price',7,2)->nullable();
            $table->decimal('tax',7,2)->nullable();
            $table->decimal('taxRate',4,2)->nullable();
            $table->integer('mediaId')->nullable();
            $table->boolean('isCustomVariant')->nullable();
            $table->boolean('isUploaded')->nullable();
            $table->boolean('isPublished')->nullable();
            $table->char('ean', 15)->unique()->nullable();
            $table->timestamps();

            $table->foreign('productId')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('variantId');
            $table->string('key');
            $table->string('value');

            $table->foreign('variantId')
                    ->references('id')
                    ->on('variants')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('views', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('variantId');

            $table->foreign('variantId')
                    ->references('id')
                    ->on('variants')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('front', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->string('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('back', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->string('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('left', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->string('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('right', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('viewId');
            $table->string('compositeMediaId');

            $table->foreign('viewId')
                    ->references('id')
                    ->on('views')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('frontCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('frontId');
            $table->string('type')->nullable();
            $table->string('productionMediaId')->nullable();
            $table->string('previewMediaId')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();

            $table->foreign('frontId')
                    ->references('id')
                    ->on('front')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('backCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('backId');
            $table->string('type')->nullable();
            $table->string('productionMediaId')->nullable();
            $table->string('previewMediaId')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();

            $table->foreign('backId')
                    ->references('id')
                    ->on('back')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('leftCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('leftId');
            $table->string('type')->nullable();
            $table->string('productionMediaId')->nullable();
            $table->string('previewMediaId')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();

            $table->foreign('leftId')
                    ->references('id')
                    ->on('left')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });

        Schema::create('rightCustomizations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rightId');
            $table->string('type')->nullable();
            $table->string('productionMediaId')->nullable();
            $table->string('previewMediaId')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();

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
