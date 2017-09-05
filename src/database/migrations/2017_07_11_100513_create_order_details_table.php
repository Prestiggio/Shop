<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("order_id", false, true);
            $table->integer("order_invoice_id", false, true);
            $table->integer("warehouse_id", false, true)->nullable();
            $table->integer("shop_id", false, true);
            $table->integer("sellable_id", false, true);
            $table->char("sellable_name");
            $table->integer("quantity", false, true);
            $table->char("unit", 20)->nullable();
            $table->integer("quantity_in_stock", false, true);
            $table->integer("quantity_refunded", false, true);
            $table->integer("quantity_return", false, true);
            $table->integer("quantity_reinjected", false, true);
            $table->decimal("price", 20, 6);
            $table->decimal("reduction_percent", 10, 2);
            $table->decimal("reduction_amount", 20, 2);
            $table->decimal("reduction_amount_tax_incl", 20, 2);
            $table->decimal("reduction_amount_tax_excl", 20, 2);
            $table->decimal("group_reduction", 20, 2);
            $table->decimal("quantity_discount", 20, 2);
            $table->char("ean13", 13)->nullable();
            $table->char("isbn", 13)->nullable();
            $table->char("upc", 12)->nullable();
            $table->char("reference", 32)->nullable();
            $table->char("supplier_reference", 32)->nullable();
            $table->decimal("product_weight", 20, 6)->nullable();
            $table->char("tax_name", 16);
            $table->decimal("tax_rate", 10, 3);
            $table->decimal("ecotax", 21, 6);
            $table->decimal("ecotax_tax_rate", 5, 3);
            $table->boolean("discount_quantity_applied");
            $table->char("download_hash")->nullable();
            $table->integer("download_nb", false, true);
            $table->dateTime("download_deadline");
            $table->decimal("total_price_tax_incl", 20, 6);
            $table->decimal("total_price_tax_excl", 20, 6);
            $table->decimal("unit_price_tax_incl", 20, 6);
            $table->decimal("unit_price_tax_excl", 20, 6);
            $table->decimal("purchase_supplier_price", 20, 6);
            $table->decimal("original_product_price", 20, 6);
            $table->decimal("original_wholesale_price", 20, 6);
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
        Schema::drop('ry_shop_order_details');
    }
}
