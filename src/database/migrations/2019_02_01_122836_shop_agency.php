<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShopAgency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_bank_agencies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id', false, true);
            $table->string('name')->nullable();
            $table->integer('adresse_id', false, true);
            $table->timestamps();
            
            $table->foreign("adresse_id")->references("id")->on("ry_geo_adresses")->onDelete("cascade");
            $table->foreign("bank_id")->references("id")->on("ry_shop_banks")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_shop_bank_agencies');
    }
}
