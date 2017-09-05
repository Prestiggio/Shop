<?php

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
            $table->integer("order_id", false, true);
            $table->dateTime("delivery_date");
            $table->decimal("total_discounts_tax_incl", 20, 6);
            $table->decimal("total_discounts_tax_excl", 20, 6);
            $table->decimal("total_paid_tax_incl", 20, 6);
            $table->decimal("total_paid_tax_excl", 20, 6);
            $table->decimal("total_products", 20, 6);
            $table->decimal("total_products_wt", 20, 6);
            $table->decimal("total_wrapping_tax_incl", 20, 6);
            $table->decimal("total_wrapping_tax_excl", 20, 6);
            $table->text("shop_adresse")->nullable();
            $table->text("note")->nullable();
            $table->timestamps();
            
            $table->unique("order_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_order_invoices');
    }
}
