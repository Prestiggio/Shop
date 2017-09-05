<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricedIntentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_priced_intentions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("order_detail_id", false, true);
            $table->morphs("intended");
            $table->text("redirection_uri");
            $table->decimal("price", 20, 6);
           	$table->integer("currency_id", false, true);
           	$table->integer("quantity", false, true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign("order_detail_id")->references("id")->on("ry_shop_order_details")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("ry_shop_currencies");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_priced_intentions');
    }
}
