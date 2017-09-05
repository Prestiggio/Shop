<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_pack_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("pack_id", false, true);
            $table->text("vendible_type");
            $table->integer("quantity", false, true);
            $table->timestamps();
            
            $table->foreign("pack_id")->references("id")->on("ry_shop_packs")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_pack_items');
    }
}
