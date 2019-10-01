<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("cart_id")->nullable();
            $table->morphs('buyer', 'order_buyer');
            $table->unsignedInteger("shop_id")->nullable();
            $table->morphs('seller', 'order_seller');
            /**
            $setup = [
                 "delivery_at" => "date",
                 "invoice_at" => "date",
                 "invoice_number",
                 "delivery_number",
                 "round_mode",
                 "round_type",
                 "conversion_rate",
                 "currency_id",
                 "discounts" => [
                      [
                          "tax_incl",
                          "tax_excl",
                          "amount"
                      ]
                 ],
                 "wrapping" => [
                      "tax_incl",
                      "tax_excl",
                      "due"
                 ],
                 "total_quantity" => [
                      [
                          "unit", //weight, number, volume...
                          "value"
                      ]
                 ],
                 "status",
                 "recyclable",
                 "payments" => [
                      [
                           "tax_incl",
                           "tax_excl",
                           "amount",
                           "real_amount"
                      ]
                 ],
                 "delivery" => [
                      "profile",
                      "contacts",
                      "adresse"
                 ],
                 "invoice" => [
                      "profile",
                      "contacts",
                      "adresse"
                 ]
            ];
             */
            $table->json('setup');
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
        Schema::dropIfExists('ry_shop_orders');
    }
}
