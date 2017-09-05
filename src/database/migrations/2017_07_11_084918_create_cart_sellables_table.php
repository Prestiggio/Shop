<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartSellablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_cart_sellables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("cart_id", false, true);
            $table->integer("sellable_id", false, true);
            $table->integer("delivery_adresse_id", false, true)->nullable();
            $table->integer("shop_id", false, true);
            $table->integer("quantity", false, true);
            $table->char("unit", 20)->nullable();
            $table->timestamps();
            
            $table->unique(["cart_id", "sellable_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_cart_sellables');
    }
}
