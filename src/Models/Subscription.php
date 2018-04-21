<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
	use SoftDeletes;
	
    protected $table = "ry_shop_subscriptions";
    
    //protected $with = ["packItem"];
    
    protected $appends = ["expiry_diff"];
    
    protected $dates = ["expiry"];
    
    public function owner() {
    	return $this->belongsTo("Ry\Shop\Models\Customer", "customer_id");
    }
    
    public function orderDetail() {
    	return $this->belongsTo("Ry\Shop\Models\OrderDetail", "order_detail_id");
    }
    
    public function packItem() {
    	return $this->belongsTo("Ry\Shop\Models\PackItem", "pack_item_id");
    }
    
    public function getExpiryDiffAttribute() {
    	Carbon::setLocale('fr');
    	return $this->expiry->diffForHumans();
    }
}
