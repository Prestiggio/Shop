<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = "ry_shop_customers";
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function facturable() {
    	return $this->morphTo();
    }
    
    public function owner() {
    	return $this->facturable();
    }
    
    public function currency() {
    	return $this->hasOne("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function carts() {
    	return $this->hasMany("Ry\Shop\Models\Cart", "customer_id");
    }
}
