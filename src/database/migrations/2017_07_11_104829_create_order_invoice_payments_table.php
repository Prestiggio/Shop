<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_order_invoice_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("order_invoice_id", false, true);
            $table->integer("order_payment_id", false, true);
            $table->timestamps();
            
            $table->unique(["order_invoice_id", "order_payment_id"], "unique_payment_order");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_order_invoice_payments');
    }
}
