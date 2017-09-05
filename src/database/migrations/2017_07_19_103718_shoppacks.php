<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Shoppacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_packs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("offer_id", false, true);
            $table->timestamps();
            
            $table->foreign("offer_id")->references("id")->on("ry_shop_offers")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_packs');
    }
}
