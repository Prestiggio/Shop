<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopPerorderdetailsNoneedinvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ry_shop_order_details', function (Blueprint $table) {
            $table->dropForeign("ry_shop_order_details_order_invoice_id_foreign");
            $table->dropIndex("ry_shop_order_details_order_invoice_id_foreign");
            $table->dropColumn("order_invoice_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ry_shop_order_details', function (Blueprint $table) {
        	$table->integer("order_invoice_id", false, true)->after("order_id");
        	$table->foreign("order_invoice_id")->references("id")->on("ry_shop_order_invoices");
        });
    }
}
