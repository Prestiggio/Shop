<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Ry\Analytics\Models\Traits\LinkableTrait;
use Ry\Admin\Models\Traits\HasJsonSetup;
use App\User;
use Ry\Shop\Models\Traits\HasBankAccounts;

class Customer extends Model
{
	use LinkableTrait, HasJsonSetup, HasBankAccounts;

    protected $table = "ry_shop_customers";
    
    //protected $with = ["subscriptions"];
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function facturable() {
    	return $this->morphTo();
    }
    
    public function author() {
        return $this->belongsTo(User::class, 'author_id');
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
					if($subscription->packItem->pack->offer->type=='abonnement')
						return true;
					if($subscription->remainder > 0)
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
	
	public function getSlugAttribute() {
    	if($this->slugs()->exists())
    		return $this->slugs->slug;
    	
    	return str_random(16);
    }
}
