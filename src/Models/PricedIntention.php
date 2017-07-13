<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PricedIntention extends Model
{
    protected $table = "ry_shop_priced_intentions";
    
    public function intended() {
    	return $this->morphTo();
    }
    
    public function invoice() {
    	return $this->belongsTo("Ry\Shop\Models\OrderInvoice", "order_invoice_id");
    }
}
