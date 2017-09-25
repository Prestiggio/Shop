<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
	use SoftDeletes;
	
    protected $table = "ry_shop_orders";
    
    protected $dates = ["delivery_date", "invoice_date", "deleted_at"];
    
    public function cart() {
    	return $this->belongsTo("Ry\Shop\Models\Cart", "cart_id");
    }
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function deliveryAdresse() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "delivery_adresse_id");
    }
    
    public function invoiceAdresse() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "invoice_adresse_id");
    }
    
    public function currency() {
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function items() {
    	return $this->hasMany("Ry\Shop\Models\OrderDetail", "order_id");
    }
    
    public function invoices() {
    	return $this->hasMany("Ry\Shop\Models\OrderInvoice", "order_id");
    }
}
