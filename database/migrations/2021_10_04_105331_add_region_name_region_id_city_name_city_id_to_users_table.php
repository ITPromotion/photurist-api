<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionNameRegionIdCityNameCityIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('region_id')->after('country_name')->nullable();
            $table->string('region_name')->after('region_id')->nullable();
            $table->string('city_id')->after('region_name')->nullable();
            $table->string('city_name')->after('city_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'region_id',
                'region_name',
                'city_id',
                'city_name',
            ]);
        });
    }
}
