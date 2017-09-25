<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SubscriptionExpiry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ry_shop_subscriptions', function (Blueprint $table) {
            $table->dateTime("expiry")->after("remainder");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ry_shop_subscriptions', function (Blueprint $table) {
            //
        });
    }
}
