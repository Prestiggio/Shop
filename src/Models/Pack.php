<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = "ry_shop_packs";
    
    protected $with = ["items"];
    
    public function offer() {
    	return $this->belongsTo("Ry\Shop\Models\Offer", "offer_id");
    }
    
    public function items() {
    	return $this->hasMany("Ry\Shop\Models\PackItem", "pack_id");
    }
}
