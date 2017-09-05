<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs("facturable");
            $table->integer("shop_id", false, true);
            $table->decimal("outstanding_allow_amount", 20, 6);
            $table->boolean("show_public_prices")->default(0);
            $table->integer("max_payment_days", false, true)->default(60);
            $table->text("note")->nullable();
            $table->boolean("active")->default(0);
            $table->boolean("is_guest")->default(0);
            $table->timestamps();
            
            $table->unique(["facturable_id", "facturable_type"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ry_shop_customers');
    }
}
