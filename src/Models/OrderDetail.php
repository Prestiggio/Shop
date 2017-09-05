<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = "ry_shop_order_details";
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function sellable() {
    	return $this->belongsTo("Ry\Shop\Models\Sellable", "sellable_id");
    }
    
}
