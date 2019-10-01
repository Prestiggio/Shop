<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("shop_id", false, true)->nullable();
            $table->integer("delivery_adresse_id", false, true)->nullable();
            $table->integer("invoice_adresse_id", false, true)->nullable();
            $table->integer("currency_id", false, true);
            $table->integer("customer_id", false, true);
            $table->boolean("recyclable")->default(true);
            $table->json('setup')->nullable();
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
        Schema::drop('ry_shop_carts');
    }
}
