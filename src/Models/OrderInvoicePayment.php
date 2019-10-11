<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderInvoicePayment extends Model
{
    protected $table = "ry_shop_order_invoice_payments";
    
    public function invoice() {
        return $this->belongsTo(OrderInvoice::class, 'order_invoice_id');
    }
}
