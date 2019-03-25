<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('organizationName');
            $table->string('street');
            $table->string('houseNr');
            $table->string('postalCode');
            $table->string('city');
            $table->string('email');
            $table->string('phone');
            $table->string('cocNr');
            $table->string('vatNr');
            $table->string('appSerNr');
            $table->string('systemKey');
            $table->text('apiKeyBol');
            $table->text('apiKeySmake');
            $table->string('logo_id')->nullable();
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
        Schema::dropIfExists('systems');
    }
}
