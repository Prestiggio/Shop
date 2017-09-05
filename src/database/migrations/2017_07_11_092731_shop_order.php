<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopOrder extends Migration
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
            $table->char("reference", 9);
            $table->integer("cart_id", false, true);
            $table->integer("shop_id", false, true)->nullable();            
            $table->integer("delivery_adresse_id", false, true)->nullable();
            $table->integer("invoice_adresse_id", false, true)->nullable();
            $table->integer("currency_id", false, true);
            $table->boolean("recyclable")->default(true);
            $table->integer("current_state", false, true);
            $table->char("payment");
            $table->decimal("conversion_rate", 13, 6)->default(1);
            $table->decimal("total_discounts", 20, 6);
            $table->decimal("total_discounts_tax_incl", 20, 6);
            $table->decimal("total_discounts_tax_excl", 20, 6);
            $table->decimal("total_paid", 20, 6);
            $table->decimal("total_paid_tax_incl", 20, 6);
            $table->decimal("total_paid_tax_excl", 20, 6);
            $table->decimal("total_paid_real", 20, 6);
            $table->decimal("total_products", 20, 6);
            $table->decimal("total_products_wt", 20, 6);
            $table->decimal("total_wrapping", 20, 6);
            $table->decimal("total_wrapping_tax_incl", 20, 6);
            $table->decimal("total_wrapping_tax_excl", 20, 6);
            $table->tinyInteger("round_mode");
            $table->tinyInteger("round_type");
            $table->integer("invoice_number", false, true);
            $table->integer("delivery_number", false, true);
            $table->dateTime("invoice_date")->nullable();
            $table->dateTime("delivery_date")->nullable();
            $table->boolean("valid");
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique("cart_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_orders');
    }
}
