<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_data', function (Blueprint $table) {
            $table->id();
            $table->integer('lat')->nullable();
            $table->integer('lng')->nullable();
            $table->integer('address')->nullable();
            $table->unsignedBigInteger('postcard_id')->nullable();
            $table->foreign('postcard_id')->references('id')->on('postcards')->onDelete('cascade');
            $table->unsignedBigInteger('media_content_id')->nullable();
            $table->foreign('media_content_id')->references('id')->on('media_contents')->onDelete('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('geo_data');
    }
}
