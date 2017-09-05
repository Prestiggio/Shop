<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::table('ry_shop_carts', function (Blueprint $table) {
    		$table->foreign("shop_id")->references("id")->on("ry_shop_shops")->onDelete("cascade");
    		$table->foreign("delivery_adresse_id")->references("id")->on("ry_geo_adresses");
    		$table->foreign("invoice_adresse_id")->references("id")->on("ry_geo_adresses");
    		$table->foreign("currency_id")->references("id")->on("ry_shop_currencies");
    		$table->foreign("customer_id")->references("id")->on("ry_shop_customers")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_shops', function (Blueprint $table) {
    		$table->foreign("shop_group_id")->references("id")->on("ry_shop_shop_groups")->onDelete("cascade");
    		$table->foreign("owner_id")->references("id")->on("users")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_cart_sellables', function (Blueprint $table) {
    		$table->foreign("cart_id")->references("id")->on("ry_shop_carts")->onDelete("cascade");
    		$table->foreign("sellable_id")->references("id")->on("ry_shop_sellables")->onDelete("cascade");
    		$table->foreign("delivery_adresse_id")->references("id")->on("ry_geo_adresses");
    		$table->foreign("shop_id")->references("id")->on("ry_shop_shops")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_customers', function (Blueprint $table) {
    		$table->foreign("shop_id")->references("id")->on("ry_shop_shops")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_orders', function (Blueprint $table) {
    		$table->foreign("cart_id")->references("id")->on("ry_shop_carts")->onDelete("cascade");
    		$table->foreign("shop_id")->references("id")->on("ry_shop_shops")->onDelete("cascade");
    		$table->foreign("delivery_adresse_id")->references("id")->on("ry_geo_adresses");
    		$table->foreign("invoice_adresse_id")->references("id")->on("ry_geo_adresses");
    		$table->foreign("currency_id")->references("id")->on("ry_shop_currencies");
    	});
    	
    	Schema::table('ry_shop_order_details', function (Blueprint $table) {
    		$table->foreign("order_id")->references("id")->on("ry_shop_orders")->onDelete("cascade");
    		$table->foreign("order_invoice_id")->references("id")->on("ry_shop_order_invoices");
    		$table->foreign("shop_id")->references("id")->on("ry_shop_shops")->onDelete("cascade");
    		$table->foreign("sellable_id")->references("id")->on("ry_shop_sellables")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_order_invoice_payments', function (Blueprint $table) {
    		$table->foreign("order_invoice_id")->references("id")->on("ry_shop_order_invoices")->onDelete("cascade");
    		$table->foreign("order_payment_id")->references("id")->on("ry_shop_order_payments")->onDelete("cascade");
    	});
    	
    	Schema::table('ry_shop_order_payments', function (Blueprint $table) {
    		$table->foreign("currency_id")->references("id")->on("ry_shop_currencies")->onDelete("cascade");
    		$table->unique("transaction_id");
    		$table->unique("order_reference");
    	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	Schema::table('ry_shop_carts', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_carts_customer_id_foreign");
    		$table->dropForeign("ry_shop_carts_currency_id_foreign");
    		$table->dropForeign("ry_shop_carts_delivery_adresse_id_foreign");
    		$table->dropForeign("ry_shop_carts_invoice_adresse_id_foreign");
    		$table->dropForeign("ry_shop_carts_shop_id_foreign");
    		$table->dropIndex("ry_shop_carts_shop_id_foreign");
    		$table->dropIndex("ry_shop_carts_delivery_adresse_id_foreign");
    		$table->dropIndex("ry_shop_carts_invoice_adresse_id_foreign");
    		$table->dropIndex("ry_shop_carts_currency_id_foreign");
    		$table->dropIndex("ry_shop_carts_customer_id_foreign");
    	});
    	
    	Schema::table('ry_shop_shops', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_shops_shop_group_id_foreign");
    		$table->dropForeign("ry_shop_shops_owner_id_foreign");
    		$table->dropUnique("ry_shop_shops_shop_group_id_name_unique");
    	});
    	
    	Schema::table('ry_shop_cart_sellables', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_cart_sellables_shop_id_foreign");
    		$table->dropForeign("ry_shop_cart_sellables_cart_id_foreign");
    		$table->dropForeign("ry_shop_cart_sellables_delivery_adresse_id_foreign");
    		$table->dropForeign("ry_shop_cart_sellables_sellable_id_foreign");
    		$table->dropUnique("ry_shop_cart_sellables_cart_id_sellable_id_unique");
    		$table->dropIndex("ry_shop_cart_sellables_sellable_id_foreign");
    		$table->dropIndex("ry_shop_cart_sellables_delivery_adresse_id_foreign");
    		$table->dropIndex("ry_shop_cart_sellables_shop_id_foreign");
    	});
    	
    	Schema::table('ry_shop_customers', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_customers_shop_id_foreign");
    		$table->dropUnique("ry_shop_customers_facturable_id_facturable_type_unique");
    		$table->dropIndex("ry_shop_customers_facturable_id_facturable_type_index");
    		$table->dropIndex("ry_shop_customers_shop_id_foreign");
    	});
    	
    	Schema::table('ry_shop_orders', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_orders_cart_id_foreign");
    		$table->dropForeign("ry_shop_orders_currency_id_foreign");
    		$table->dropForeign("ry_shop_orders_delivery_adresse_id_foreign");
    		$table->dropForeign("ry_shop_orders_invoice_adresse_id_foreign");
    		$table->dropForeign("ry_shop_orders_shop_id_foreign");
    		$table->dropIndex("ry_shop_orders_shop_id_foreign");
    		$table->dropIndex("ry_shop_orders_delivery_adresse_id_foreign");
    		$table->dropIndex("ry_shop_orders_invoice_adresse_id_foreign");
    		$table->dropIndex("ry_shop_orders_currency_id_foreign");
    	});
    	
    	Schema::table('ry_shop_order_details', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_order_details_sellable_id_foreign");
    		$table->dropForeign("ry_shop_order_details_order_id_foreign");
    		$table->dropForeign("ry_shop_order_details_order_invoice_id_foreign");
    		$table->dropForeign("ry_shop_order_details_shop_id_foreign");
    		$table->dropIndex("ry_shop_order_details_order_id_foreign");
    		$table->dropIndex("ry_shop_order_details_order_invoice_id_foreign");
    		$table->dropIndex("ry_shop_order_details_shop_id_foreign");
    		$table->dropIndex("ry_shop_order_details_sellable_id_foreign");
    	});
    		 
    	Schema::table('ry_shop_order_invoice_payments', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_order_invoice_payments_order_invoice_id_foreign");
    		$table->dropForeign("ry_shop_order_invoice_payments_order_payment_id_foreign");
    		$table->dropUnique("unique_payment_order");
    		$table->dropIndex("ry_shop_order_invoice_payments_order_payment_id_foreign");    		
    	});
    				 
    	Schema::table('ry_shop_order_payments', function (Blueprint $table) {
    		$table->dropForeign("ry_shop_order_payments_currency_id_foreign");
    		$table->dropUnique("ry_shop_order_payments_order_reference_unique");
    		$table->dropUnique("ry_shop_order_payments_transaction_id_unique");
    		$table->dropIndex("ry_shop_order_payments_currency_id_foreign");
    	});
    }
}
