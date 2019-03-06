<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class OrderItem extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_order_items";
    
    protected $fillable = ["sellable_type", "sellable_id", "quantity", "price", "setup"];
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function sellable() {
    	return $this->morphTo();
    }
    
}
