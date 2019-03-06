<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasJsonSetup, SoftDeletes;
    
    protected $table = "ry_shop_payments";
    
    public function invoices() {
    	return $this->belongsToMany(OrderInvoice::class, "ry_shop_order_invoice_payments", "order_payment_id", "order_invoice_id");
    }
    
    public function session() {
    	return $this->belongsTo(User::class, "user_id");
    }
    
    public function currency() {
    	return $this->belongsTo(Currency::class, "currency_id");
    }
    
    public function account() {
        return $this->morphTo();
    }
}
