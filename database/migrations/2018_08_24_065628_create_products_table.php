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
            $table->string('smakeId', 25);
            $table->string('productName', 150);
            $table->double('salesprice',8,2)->nullable();
            $table->mediumText('productDescription');
            $table->timestamps();
        });

        Schema::create('variants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('productId')->nullable();
            $table->unsignedInteger('variantId')->unique()->nullable();
            $table->boolean('isBwdVariant')->nullable();
            $table->char('ean', 15)->unique()->nullable();
            $table->decimal('price',7,2)->nullable();
            $table->decimal('tax',7,2)->nullable();
            $table->decimal('taxRate',4,2)->nullable();
            $table->string('size', 5)->nullable();
            $table->string('color', 25)->nullable();
            $table->integer('mediaId')->nullable();
            $table->string('localMediaFileName', 100)->nullable();
            $table->string('smallFileName', 100)->nullable();
            $table->boolean('isCustomVariant')->nullable();
            $table->boolean('isUploaded')->nullable();
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
            $table->string('key', 100);
            $table->string('value', 100);

            $table->foreign('variantId')
                    ->references('id')
                    ->on('variants')
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
        // Schema::dropIfExists('rightCustomizations');
        // Schema::dropIfExists('frontCustomizations');
        // Schema::dropIfExists('leftCustomizations');
        // Schema::dropIfExists('backCustomizations');
        // Schema::dropIfExists('right');
        // Schema::dropIfExists('front');
        // Schema::dropIfExists('left');
        // Schema::dropIfExists('back');
        // Schema::dropIfExists('views');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('products');
    }
}
