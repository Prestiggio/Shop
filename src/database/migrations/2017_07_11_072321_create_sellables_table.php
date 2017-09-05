<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_sellables', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('sellable');
            $table->timestamps();
            
            $table->unique(["sellable_id", "sellable_type"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_sellables');
    }
}
