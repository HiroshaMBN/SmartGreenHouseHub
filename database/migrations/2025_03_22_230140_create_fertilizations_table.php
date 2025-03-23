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
        Schema::create('fertilizations', function (Blueprint $table) {
            $table->id();
            $table->string("fertilize_name")->nullable();
            $table->float("availability")->nullable();
            $table->string("unit_type")->nullable();
            $table->date("next_stock_date")->nullable();
            $table->float("unit_price")->nullable();
            $table->float("total")->nullable();
            $table->string("stock_level")->nullable();
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
        Schema::dropIfExists('fertilizations');
    }
};
