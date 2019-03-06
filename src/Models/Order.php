<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class Order extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_orders";
    
    public function cart() {
    	return $this->belongsTo("Ry\Shop\Models\Cart", "cart_id");
    }
    
    public function shop() {
        return $this->belongsTo(Shop::class, "shop_id");
    }
    
    public function buyer() {
    	return $this->morphTo();
    }
    
    public function seller() {
        return $this->morphTo();
    }
    
    public function items() {
    	return $this->hasMany(OrderItem::class, "order_id");
    }
    
    public function invoices() {
    	return $this->hasMany("Ry\Shop\Models\OrderInvoice", "order_id");
    }
}
