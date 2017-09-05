<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_shops', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("shop_group_id", false, true);
            $table->integer("owner_id", false, true);
            $table->char("name", 64);
            $table->boolean("active")->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(["shop_group_id", "name"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_shops');
    }
}
