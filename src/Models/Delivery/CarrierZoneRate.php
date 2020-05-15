<?php

namespace Ry\Shop\Models\Delivery;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Ry\Shop\Models\Traits\SellableTrait;

class CarrierZoneRate extends Model
{
    use HasJsonSetup, SellableTrait;
    
    protected $table = "ry_shop_carrier_zone_rates";
    
    public function carrier() {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }
}
