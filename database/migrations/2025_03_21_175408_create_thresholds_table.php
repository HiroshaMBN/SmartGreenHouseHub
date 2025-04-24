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
            $table->string('normal');
            $table->string('warning');
            $table->string('critical');
            $table->string('is_enable_notify');
            $table->bool('is_normal');
            $table->bool('is_warning');
            $table->bool('is_critical');
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
