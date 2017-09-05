<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("customer_id", false, true);
            $table->integer("order_detail_id", false, true);
            $table->integer("pack_item_id", false, true);
            $table->integer("remainder", false, true);
            $table->timestamps();
            
            $table->foreign("customer_id")->references("id")->on("ry_shop_customers")->onDelete("cascade");
            $table->foreign("order_detail_id")->references("id")->on("ry_shop_order_details")->onDelete("cascade");
            $table->foreign("pack_item_id")->references("id")->on("ry_shop_pack_items");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_subscriptions');
    }
}
