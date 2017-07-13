<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = "ry_shop_order_payments";
    
    public function currency() {
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
}
