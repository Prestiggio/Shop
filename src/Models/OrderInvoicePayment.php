<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderInvoicePayment extends Model
{
    protected $table = "ry_shop_order_invoice_payments";
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function invoice() {
    	return $this->hasOne("Ry\Shop\Models\OrderInvoice", "order_invoice_id");
    }
    
    public function payment() {
    	return $this->hasOne("Ry\Shop\Models\OrderPayment", "order_payment_id");
    }
}
