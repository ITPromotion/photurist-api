<?php

use App\Enums\PostcardStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDraftToPostcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postcards', function (Blueprint $table) {
            $table->boolean('draft')->default(true);
        });

        DB::table('postcards')
            ->where('status',PostcardStatus::ACTIVE)
            ->orWhere('status',PostcardStatus::ARCHIVE)
            ->update(['draft' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postcards', function (Blueprint $table) {
            $table->dropColumn('draft');
        });
    }
}
