<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class OrderPayment extends Model
{
    protected $table = "ry_shop_order_payments";
    
    public function currency() {
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function getInvoicesAttribute() {
    	return OrderInvoicePayment::where("order_payment_id", "=", $this->id)->first()->invoices;
    }
    
    public function getItemsAttribute() {
    	$ar = [];
    	foreach($this->invoices as $invoice) {
    		foreach($invoice->order->items as $item) {
    			$ar[] = $item->sellable->sellable;
    		}
    	}
    	return new Collection($ar);
    }
    
    public function getAuthorAttribute() {
    	foreach($this->invoices as $invoice) {
    		return $invoice->order->cart->customer->owner;
    	}
    }
}
