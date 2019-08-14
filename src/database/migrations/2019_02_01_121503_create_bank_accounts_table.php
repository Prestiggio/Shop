<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_shop_bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("bank_id");
            $table->morphs("bankable");
            $table->text("setup");
            $table->unsignedInteger("currency_id");
            $table->timestamps();
            
            $table->foreign("currency_id")->references("id")->on("ry_shop_currencies")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_shop_bank_accounts');
    }
}
