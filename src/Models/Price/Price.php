<?php

namespace Ry\Shop\Models\Price;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class Price extends Model
{
    use HasJsonSetup;
    
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
}
