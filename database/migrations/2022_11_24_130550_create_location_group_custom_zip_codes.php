<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationGroupCustomZipCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_group_custom_zip_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('location_group_id');
            $table->string('country');
            $table->string('state_name');
            $table->string('county_name');
            $table->string('city_name');
            $table->text('zip_codes');
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
        Schema::dropIfExists('location_group_custom_zip_codes');
    }
}
