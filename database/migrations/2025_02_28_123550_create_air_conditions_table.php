<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('air_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('instance_id')->nullable();
            $table->string('sensor_id')->nullable();
            $table->float('value')->nullable();
            $table->float('status')->nullable();
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
        Schema::dropIfExists('air_conditions');
    }
};
