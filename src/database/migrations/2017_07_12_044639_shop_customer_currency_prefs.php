<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopCustomerCurrencyPrefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ry_shop_customers', function (Blueprint $table) {
            $table->integer("currency_id", false, true)->after("shop_id");
            $table->foreign("currency_id")->references("id")->on("ry_shop_currencies")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ry_shop_customers', function (Blueprint $table) {
            $table->dropForeign("ry_shop_customers_currency_id_foreign");
            $table->dropIndex("ry_shop_customers_currency_id_foreign");
            $table->dropColumn(["currency_id"]);
        });
    }
}
