<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryKeyToPostcardsMailings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postcards_mailings', function (Blueprint $table) {
            $table->primary(['postcard_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postcards_mailings', function (Blueprint $table) {
           $table->dropPrimary(['postcard_id', 'user_id']);
        });
    }
}
