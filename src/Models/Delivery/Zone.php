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
        static::addGlobalScope("active", function($q){
            $q->join("ry_centrale_site_restrictions", "ry_centrale_site_restrictions.scope_id", "=", "ry_shop_delivery_zones.id")
            ->whereScopeType(static::class)
            ->where("ry_centrale_site_restrictions.setup->active", true)
            ->select("ry_shop_delivery_zones.*");
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
