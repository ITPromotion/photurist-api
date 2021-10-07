<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRadiusCountriesRegionsCitiesLatLngToPostcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postcards', function (Blueprint $table) {
            $table->bigInteger('radius')->nullable();
            $table->float('lat')->nullable();
            $table->float('lng')->nullable();
            $table->json('countries')->nullable();
            $table->json('regions')->nullable();
            $table->json('cities')->nullable();

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
               'radius',
               'countries',
               'regions',
               'cities',
            ]);
        });
    }
}
