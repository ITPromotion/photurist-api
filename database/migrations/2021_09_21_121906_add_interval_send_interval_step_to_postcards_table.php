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
            $table->time('interval_send')->after('status')->nullable();
            $table->time('interval_step')->after('interval_send')->nullable();
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
                'interval_step',
            ]);
        });
    }
}
