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
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('sensor_name');
            $table->boolean('is_automate')->default(1);
            $table->string('normal')->default(0);
            $table->string('warning')->default(0);
            $table->string('description');
            $table->string('critical')->default(0);
            $table->string('is_enable_notify');
            $table->boolean('is_normal')->default(0);
            $table->boolean('is_warning')->default(0);
            $table->boolean('is_critical')->default(0);
            $table->integer('stop_limit')->default(1);
            $table->string('notify_type');
            $table->integer('count');
            $table->string('notify_interval');
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
        Schema::dropIfExists('thresholds');
    }
};
