<?php

namespace Ry\Shop\Models\Price;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\Currency;

class Price extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_prices";
    
    protected $fillable = ["price", "currency_id", "prefix", "suffix"];
    
    public function priceable() {
        return $this->morphTo();
    }
    
    public function shop() {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
    
    public function getNsetupAttribute() {
        if($this->setup)
            return json_decode($this->setup, true);
        return [
            'period' => 'monthly',
            'mode' => ''
        ];
    }
    
    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
