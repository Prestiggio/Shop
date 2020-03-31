<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->morphs('priceable');
            $table->decimal("price", 20, 8);
            $table->text('setup')->nullable();
            $table->unsignedInteger("currency_id");
            $table->char("prefix")->nullable();
            $table->char("suffix")->nullable();
            $table->timestamps();
            
            $table->unique(['priceable_type', 'priceable_id', 'price', 'currency_id'], 'price_localized_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_shop_prices');
    }
}
