<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopSellableMultiple extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ry_shop_sellables', function (Blueprint $table) {
            $table->boolean("multiple")->after("sellable_type")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ry_shop_sellables', function (Blueprint $table) {
            $table->dropColumn("multiple");
        });
    }
}
