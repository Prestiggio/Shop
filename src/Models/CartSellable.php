<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class CartSellable extends Model
{
    protected $table = "ry_shop_cart_sellables";
    
    //protected $with = ["sellable", "deliveryAdresse"];
    
    public function sellable() {
        return $this->morphTo();
    }
    
    public function cart() {
    	return $this->belongsTo("Ry\Shop\Models\Cart", "cart_id");
    }
    
    public function deliveryAdresse() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "delivery_adresse_id");
    }
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
}
