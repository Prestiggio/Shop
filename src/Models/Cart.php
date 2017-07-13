<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = "ry_shop_carts";
    
    protected $with = ["items", "shop", "deliveryAddress", "invoiceAddress", "currency", "customer"];
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function deliveryAddress() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "delivery_adresse_id");
    }
    
    public function invoiceAddress() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "invoice_adresse_id");
    }
    
    public function currency() {
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function customer() {
    	return $this->belongsTo("Ry\Shop\Models\Customer", "customer_id");
    }
    
    public function items() {
    	return $this->hasMany("Ry\Shop\Models\CartSellable", "cart_id");
    }
    
    public function order() {
    	return $this->hasOne("Ry\Shop\Models\Order", "cart_id");
    }
    
    public function getAdminUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\AdminController@getCart") . "?id=" . $this->id;
    }
}
