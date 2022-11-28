<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->integer('status');
            $table->string('country');
            $table->string('state_name');
            $table->string('state_short_name');
            $table->string('county_name');
            $table->string('city_name');
            $table->string('zip_code');
            $table->string('state_lat');
            $table->string('state_lng');
            $table->string('county_lat');
            $table->string('county_lng');
            $table->string('city_lat');
            $table->string('city_lng');
            $table->string('zip_lat');
            $table->string('zip_lng');
            $table->string('timezone');
            $table->string('area_code');
            $table->integer('zip_population');
            $table->string('type');
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
        Schema::dropIfExists('locations');
    }
}
