<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Analytics\Models\Traits\LinkableTrait;

class OrderInvoice extends Model
{
	use LinkableTrait;
	
    protected $table = "ry_shop_order_invoices";
    
    protected $with = ["payments"];
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function getDetailUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\UserController@invoiceDetail", ["invoice" => $this]);
    }
    
    public function getSlugAttribute() {
    	if($this->slugs()->exists())
    		return $this->slugs->slug;
    	
    	return str_random(16);
    }
    
    public function getAdminUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\AdminController@getInvoice") . "?id=" . $this->id;
    }
    
    public function payments() {
    	return $this->belongsToMany("Ry\Shop\Models\OrderPayment", "ry_shop_order_invoice_payments", "order_invoice_id", "order_payment_id");
    }
}
