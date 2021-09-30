<?php

use App\Enums\MailingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostcardsMailingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postcards_mailings', function (Blueprint $table) {
            $table->bigInteger('postcard_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('postcard_id')->references('id')->on('postcards');
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('status', MailingType::keys())->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('stop')->nullable();
            $table->unique(['postcard_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postcards_mailings');
    }
}
