<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntervalSendIntervalStepToPostcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postcards', function (Blueprint $table) {
            $table->bigInteger('interval_send')->after('status')->nullable();
            $table->bigInteger('interval_wait')->after('interval_send')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postcards', function (Blueprint $table) {
            $table->dropColumn([
                'interval_send',
                'interval_wait',
            ]);
        });
    }
}
