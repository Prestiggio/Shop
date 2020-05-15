<?php

namespace Ry\Shop\Models\Delivery;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class CarrierPath extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_carrier_paths";
}
