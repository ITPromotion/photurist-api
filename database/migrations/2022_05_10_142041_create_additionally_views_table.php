<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionallyViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additionally_views', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('postcard_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('postcard_id')->references('id')->on('postcards');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['postcard_id','user_id']);
            $table->boolean('view')->default(false);
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
        Schema::dropIfExists('additionally_views');
    }
}
