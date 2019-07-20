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
            $table->string('organizationName', 150);
            $table->string('street', 150);
            $table->string('houseNr', 100);
            $table->string('postalCode', 7);
            $table->string('city', 150);
            $table->string('email', 100);
            $table->string('phone', 25);
            $table->string('cocNr', 25);
            $table->string('vatNr', 100);
            $table->string('appSerNr', 100);
            $table->string('systemKey', 150);
            $table->text('apiKeyBol');
            $table->text('apiKeySmake');
            $table->string('logo_id', 100)->nullable();
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
