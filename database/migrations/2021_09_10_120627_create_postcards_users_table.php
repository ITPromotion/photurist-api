<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostcardsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postcards_users', function (Blueprint $table) {
            $table->bigInteger('postcard_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('postcard_id')->references('id')->on('postcards');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postcards_users');
    }
}
