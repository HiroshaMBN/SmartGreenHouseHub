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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id')->autoIncrement();
            $table->string('instance_id')->nullable();
            $table->string('contact_id')->nullable();;
            $table->string('user')->nullable();;
            $table->string('mobile')->nullable();;
            $table->string('email')->nullable();;
            $table->string('message')->nullable();
            $table->string('Type')->nullable();
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
        Schema::dropIfExists('notifications');
    }
};
