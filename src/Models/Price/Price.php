<?php

namespace Ry\Shop\Models\Price;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = "ry_shop_prices";
    
    protected $fillable = ["price", "currency_id", "prefix", "suffix"];
    
    public function priceable() {
        return $this->morphTo();
    }
    
    public function getNsetupAttribute() {
        if($this->setup)
            return json_decode($this->setup, true);
        return [
            'period' => 'monthly',
            'mode' => ''
        ];
    }
    
    public function setNsetupAttribute($ar) {
        $this->setup = json_encode($ar);
    }
}
