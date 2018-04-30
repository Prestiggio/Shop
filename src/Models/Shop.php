<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Medias\Models\Traits\MediableTrait;
use Ry\Geo\Models\Traits\Geoable;
use Ry\Profile\Models\Traits\JoinableTrait;

class Shop extends Model
{
	use MediableTrait, Geoable, JoinableTrait;
	
	private static $instance;
	
	protected $table = "ry_shop_shops";
	
    public function group() {
		return $this->belongsTo("Ry\Shop\Models\ShopGroup", "shop_group_id");
	}
	
	public static function current() {
		return self::$instance;
	}
	
	public static function setCurrent($shop) {
		return self::$instance = $shop;
	}
	
	public function owner() {
		return $this->belongsTo("App\User", "owner_id");
	}
	
}
