<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_order_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->char("order_reference", 9);
            $table->integer("currency_id", false, true);
            $table->decimal("amount", 10, 2);
            $table->char("payment_method");
            $table->decimal("conversion_rate", 13, 6);
            $table->char("transaction_id")->nullable();
            $table->char("card_number")->nullable();
            $table->char("card_brand")->nullable();
            $table->char("card_expiration", 7)->nullable();
            $table->char("card_holder")->nullable();
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
        Schema::drop('ry_shop_order_payments');
    }
}
