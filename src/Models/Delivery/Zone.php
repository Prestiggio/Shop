<?php

namespace Ry\Shop\Models\Delivery;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Facades\DB;
use Ry\Centrale\Models\Traits\SiteScoped;

class Zone extends Model
{
    use HasJsonSetup, SiteScoped;
    
    protected $table = "ry_shop_delivery_zones";
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope("ranked", function($q){
            $q->orderBy(DB::raw("ry_shop_delivery_zones.setup->'$.rank'"));
        });
    }
    
    public function carriers() {
        return $this->belongsToMany(Carrier::class, "ry_shop_carrier_zone_rates", "zone_id", "carrier_id")->withPivot([
            "setup"
        ]);
    }
    
    public function rates() {
        return $this->hasMany(CarrierZoneRate::class, "zone_id");
    }
}
