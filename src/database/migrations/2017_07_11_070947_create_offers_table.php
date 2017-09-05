<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_realestate_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("owner_id", false, true);
            $table->char("wpblog_url");
            $table->enum("type", ["clic", "forfait", "abonnement"])->default("clic");
            $table->char("period")->nullable();
            $table->decimal("price", 9, 2);
            $table->integer("currency_id", false, true);
            $table->timestamps();
            
            $table->foreign("owner_id")->references("id")->on("users")->onDelete("cascade");
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
    	Schema::table('ry_realestate_offers', function (Blueprint $table) {
    		$table->dropForeign("ry_realestate_offers_owner_id_foreign");
    		$table->dropIndex("ry_realestate_offers_owner_id_foreign");
    		$table->dropUnique("ry_realestate_offers_wpblog_url_type_period_unique");
    	});
        Schema::drop('ry_realestate_offers');
    }
}
