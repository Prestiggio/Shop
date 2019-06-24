<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class PricedIntention extends Model
{
    protected $table = "ry_shop_priced_intentions";
    
    public function intended() {
    	return $this->morphTo();
    }
}
