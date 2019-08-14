<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopBank extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_banks', function (Blueprint $table) {
            $table->increments('id');
            $table->text("name");
            $table->integer("adresse_id", false, true);
            $table->timestamps();
            
            $table->foreign("adresse_id")->references("id")->on("ry_geo_countries")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_shop_banks');
    }
}
