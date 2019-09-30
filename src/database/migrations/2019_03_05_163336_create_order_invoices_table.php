<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_order_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("order_id");
            $table->morphs('buyer', 'buyer_invoice');
            $table->morphs('seller', 'seller_invoice');
            $table->decimal('quantity', 20, 8);
            $table->decimal('total_price', 20, 8);
            /**
            $setup = [
                "quantity" => [
                    "value",
                    "unit",
                    "type"
                ],
                "discounts" => [
                    [
                        "rate",
                        "amount",
                        "tax_incl",
                        "tax_excl"
                    ]
                ],
                "wrapping" => [
                    [
                        "amount",
                        "tax_incl",
                        "tax_excl"
                    ]
                ],
                "delivery_at",
                "expire_at"
            ];
             */
            $table->json("setup");
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
        Schema::dropIfExists('ry_shop_order_invoices');
    }
}
