<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class PackItem extends Model
{
    protected $table = "ry_shop_pack_items";
    
    public function pack() {
    	return $this->belongsTo("Ry\Shop\Models\Pack", "pack_id");
    }
    
    public function getTitleAttribute() {
    	return $this->pack->offer->title;
    }
    
    public function getPriceAttribute() {
    	return $this->pack->offer->price;
    }
}
