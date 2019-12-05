<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Session;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Ry\Admin\Models\Traits\ArchivableTrait;

class Cart extends Model
{
    use HasJsonSetup, ArchivableTrait;
    
    protected $table = "ry_shop_carts";
    
    //protected $with = ["items", "shop", "deliveryAddress", "invoiceAddress", "currency", "customer"];
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function deliveryAddress() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "delivery_adresse_id");
    }
    
    public function invoiceAddress() {
    	return $this->belongsTo("Ry\Geo\Models\Adresse", "invoice_adresse_id");
    }
    
    public function currency() {
    	return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
    }
    
    public function customer() {
    	return $this->belongsTo("Ry\Shop\Models\Customer", "customer_id");
    }
    
    public function items() {
    	return $this->hasMany("Ry\Shop\Models\CartSellable", "cart_id");
    }
    
    public function order() {
    	return $this->hasOne("Ry\Shop\Models\Order", "cart_id");
    }
    
    public function getAdminUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\AdminController@getCart") . "?id=" . $this->id;
    }
    
    public static function add($sellable_type, $sellable_params, $quantity=1) {
    	if(!Session::has("cart"))
    		Session::put("cart", []);
    	 
    	$cart = Session::get("cart");
    	
    	if($sellable_type==PackItem::class) {    		
    		if(!isset($cart[PackItem::class])) {
    			$cart[PackItem::class] = [
    					"ids" => [],
    					"packs" => [],
    					"offers" => []
    			];
    		}
    		
    		if(isset($sellable_params["id"])) {
    			if(!isset($cart[PackItem::class]["ids"][$sellable_params["id"]])) {
    				$cart[PackItem::class]["ids"][$sellable_params["id"]] = 0;
    			}
    			
    			//check if offer can be multiple
    			$item = PackItem::where("id", "=", $sellable_params["id"])->first();
    			if(!$item)
    				abort(404);
    			
    			if($item->pack->offer->multiple)
    				$cart[PackItem::class]["ids"][$sellable_params["id"]] += $quantity;
    			else
    				$cart[PackItem::class]["ids"][$sellable_params["id"]] = 1; 
    		}
    		elseif(isset($sellable_params["pack"]["id"])) {
    			if(!isset($cart[PackItem::class]["packs"][$sellable_params["pack"]["id"]])) {
    				$cart[PackItem::class]["packs"][$sellable_params["pack"]["id"]] = 0;
    			}
    			 
    			//check if offer can be multiple
    			$pack = Pack::where("id", "=", $sellable_params["pack"]["id"])->first();
    			if(!$pack)
    				abort(404);
    			
    			if($pack->offer->multiple)
    				$cart[PackItem::class]["packs"][$sellable_params["pack"]["id"]] += $quantity;
    			else
    				$cart[PackItem::class]["packs"][$sellable_params["pack"]["id"]] = 1;
    		}
    		elseif(isset($sellable_params["pack"]["offer"]["id"])) {
    			if(!isset($cart[PackItem::class]["offers"][$sellable_params["pack"]["offer"]["id"]])) {
    				$cart[PackItem::class]["offers"][$sellable_params["pack"]["offer"]["id"]] = 0;
    			}
    			
    			$offer = Offer::where("id", "=", $sellable_params["pack"]["offer"]["id"])->first();
    			if(!$offer)
    				abort(404);
    			
    			if($offer->multiple)
    				$cart[PackItem::class]["offers"][$sellable_params["pack"]["offer"]["id"]] += $quantity;
    			else 
    				$cart[PackItem::class]["offers"][$sellable_params["pack"]["offer"]["id"]] = 1;
    		}
    		else {
    			abort(404);
    		}
    	}
    	else {
    		if(!isset($sellable_params["id"]))
    			abort(404);
    		
    		if(!isset($cart[$sellable_type])) {
    			$cart[$sellable_type] = [
    					"ids" => []
    			];
    		}
    		
    		if(!isset($cart[$sellable_type]["ids"][$sellable_params["id"]]))
    			$cart[$sellable_type]["ids"][$sellable_params["id"]] = 0;
    		
    		$cart[$sellable_type]["ids"][$sellable_params["id"]]+=$quantity;
    	}

    	Session::put("cart", $cart);
    }
    
    public static function remove($item) {
    	if(!Session::has("cart"))
    		return;
    	
    	$cart = Session::get("cart");
    	if($item["cart_type"]=="offers") {
    		unset($cart[PackItem::class][$item["cart_type"]][$item["id"]]);
    	}
    	else {
    		foreach ($cart as $sellable_type => $offer) {
    			if(isset($cart[$sellable_type][$item["cart_type"]][$item["id"]]))
    				unset($cart[$item["cart_type"]][$item["id"]]);
    		}
    	}
    	
    	Session::put("cart", $cart);
    }
    
    public static function session() {
    	$cart = Session::get("cart");
    	$ar = [];
    	foreach ($cart as $sellable_type => $offer) {
    		if(count($offer["ids"])>0) {
    			$ids = $sellable_type::whereIn("id", array_keys($offer["ids"]))->get();
    			foreach ($ids as $r) {
    				$r->cart_quantity = $offer["ids"][$r->id];
    				$ar[] = $r;
    			}
    		}
    		
    		if(isset($offer["packs"]) && count($offer["packs"])>0) {
    			$packs = Pack::whereIn("id", array_keys($offer["packs"]))->get();
    			foreach($packs as $pack) {
    				$pack->cart_quantity = $offer["packs"][$pack->id];
    				$ar[] = $pack;
    			}
    		}
    		
    		if(isset($offer["offers"]) && count($offer["offers"])>0) {
    			$offers = Offer::whereIn("id", array_keys($offer["offers"]))->get();
    			foreach($offers as $_offer) {
    				$_offer->cart_quantity = $offer["offers"][$_offer->id];
    				$ar[] = $_offer;
    			}
    		}
    	}
    	return new Collection($ar);
    }
}
