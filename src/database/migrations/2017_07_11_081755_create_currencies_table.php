<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->char("name", 64);
			$table->char("iso_code", 3);
			$table->char('symbol', 5);
			$table->decimal("conversion_rate", 13, 6);
			$table->boolean("active")->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique("iso_code");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_currencies');
    }
}
