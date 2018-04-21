<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ry\Shop\Models\Currency;
use Session;

class Sellable extends Model
{
	protected $table="ry_shop_sellables";
	
	//protected $with = ["sellable"];
	
	protected $appends = ["quantity"];
	
	private $quantity;
	
	public function sellable() {
		return $this->morphTo();
	}
	
	public function addToCart() {
		$cart = session("cart", []);
		if(!isset($cart[$this->id]))
			$cart[$this->id] = 0;
		if($this->multiple)
			$cart[$this->id]++;
		else 
			$cart[$this->id] = 1;
		
		session(["cart" => $cart]);
	}
	
	public function getQuantityAttribute() {
		return $this->quantity;
	}
	
	public function setQuantityAttribute($quantity) {
		$this->quantity = $quantity;
	}
	
	public static function createdevent($item=null) {
		if($item) {
			self::unguard();
			self::create([
					"sellable_id" => $item->id,
					"sellable_type" => get_class($item)
			]);
			self::reguard();
		}
	}
	
	public static function deletingevent($item=null) {
		self::where("sellable_id", "=", $item->id)->where("sellable_type", "=", get_class($item))->delete();
	}
	
	public static function currency() {
		$user = auth()->user();
		if($user && $user->customer_account) {
			return $user->customer_account->currency;
		}
		return Currency::first();
	}
}
