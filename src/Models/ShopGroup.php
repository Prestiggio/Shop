<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ShopGroup extends Model
{
	protected $table = "ry_shop_shop_groups";
	
    public function shops() {
		return $this->hasMany("Ry\Shop\Models\Shop", "shop_group_id");
	}
}
