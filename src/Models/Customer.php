<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Customer extends Model
{
    protected $table = "ry_shop_customers";
    
    protected $with = ["subscriptions"];
    
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
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function carts() {
    	return $this->hasMany("Ry\Shop\Models\Cart", "customer_id");
    }
    
    public function subscriptions() {
    	return $this->hasMany("Ry\Shop\Models\Subscription", "customer_id")->where("remainder", ">", 0);
    }
    
    public function isSubscribedToAny($offers) {
    	foreach ($this->subscriptions as $subscription) {
    		foreach ($offers as $offer) {
    			if($subscription->packItem->pack->offer->id==$offer->id) {
    				return true;
    			}
    		}
    	}
    	return false;
    }
    
    public function invoices() {
    	$invoices = [];
    	$carts = $this->carts()->orderBy("id", "DESC")->get();
    	foreach($carts as $cart) {
    		foreach($cart->order->invoices as $i)
    			$invoices[] = $i;
    	}
    	return new Collection($invoices);
    }
}
