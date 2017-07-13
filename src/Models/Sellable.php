<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ry\Shop\Models\Currency;

class Sellable extends Model
{
	protected $table="ry_shop_sellables";
	
	protected $with = ["sellable"];
	
	public function sellable() {
		return $this->morphTo();
	}
	
	public static function createdevent($item=null) {
		if($item) {
			Model::unguard();
			self::create([
					"sellable_id" => $item->id,
					"sellable_type" => get_class($item)
			]);
			Model::reguard();
		}
	}
	
	public static function deletedevent($item=null) {
		self::where("sellable_id", "=", $item->id)->where("sellable_type", "=", get_class($item))->delete();
	}
	
	public function newCollection(array $models = array())
	{
		return parent::newCollection(array_filter($models, function($item){
			
			
			return $item->sellable->active;
		}));
	}
	
	public static function currency() {
		$user = auth()->user();
		if($user && $user->customer_account) {
			return $user->customer_account->currency;
		}
		return Currency::first();
	}
}
