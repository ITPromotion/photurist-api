<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->bigInteger('otp_id')->unsigned();
            $table->timestamps();
            $table->foreign('otp_id')->references('id')->on('otp_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_numbers');
    }
}
