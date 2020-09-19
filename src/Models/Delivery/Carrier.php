<?php

namespace Ry\Shop\Models\Delivery;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Ry\Medias\Models\Traits\MediableTrait;

class Carrier extends Model
{
    use HasJsonSetup, MediableTrait;
    
    protected $table = "ry_shop_carriers";
    
    public function rates() {
        return $this->hasMany(CarrierZoneRate::class, 'carrier_id');
    }

}
