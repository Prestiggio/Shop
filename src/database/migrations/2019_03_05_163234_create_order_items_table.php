<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("order_id");
            $table->morphs("sellable", "order_sellable");
            $table->string('GTIN')->nullable();
            $table->decimal("quantity", 20, 8);
            $table->decimal("price", 20, 8);
            /**
            $setup = [
                "original_wholesale_price",
                "original_product_price",
                "purchase_supplier_price",
                "quantity" => [
                    [
                        "name", //weight, volume, number...
                        "unit",
                        "value"
                    ]
                ],
                "taxes" => [
                    "ecotax" => [
                        "rate",
                        "amount"
                    ]...
                ],
                "discounts" => [
                    [
                        "rate",
                        "amount",
                        "tax_incl",
                        "tax_excl"
                    ],
                    [
                        "quantity"
                    ]
                ],
                "unit_price" => [
                    "tax_incl",
                    "tax_excl",
                    "amount"
                ],
                "total_price" => [
                    "tax_incl",
                    "tax_excl",
                    "amount"
                ],
                "download_hash",
                "download_nb",
                "download_deadline",
                "discount_quantity_applied"
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
        Schema::dropIfExists('ry_shop_order_items');
    }
}
