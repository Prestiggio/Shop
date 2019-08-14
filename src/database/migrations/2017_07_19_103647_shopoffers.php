<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Shopoffers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger("author_id");
            $table->char("wpblog_url");
            $table->enum("type", ["once", "forfait", "abonnement"])->default("once");
            $table->char("period")->nullable();
            $table->integer("default_pack", false, true)->nullable();
            $table->boolean("multiple");
            $table->decimal("price", 9, 2);
            $table->integer("currency_id", false, true);
            $table->timestamps();
            
            $table->foreign("author_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("ry_shop_currencies")->onDelete("cascade");
            $table->unique(["wpblog_url", "type", "period"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_offers');
    }
}
