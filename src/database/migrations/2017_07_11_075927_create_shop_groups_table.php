<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_shop_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->char("name");
            $table->boolean("share_customer");
            $table->boolean("share_order");
            $table->boolean("share_stock");
            $table->boolean("active");
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique("name");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_shop_groups');
    }
}
