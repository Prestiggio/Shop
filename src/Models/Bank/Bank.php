<?php namespace Ry\Shop\Models\Bank;

use Illuminate\Database\Eloquent\Model;
use Ry\Geo\Models\Traits\Geoable;

class Bank extends Model {
    use Geoable;

    protected $table = "ry_shop_banks";

    public function agencies() {
        return $this->hasMany(BankAgency::class);
    }
}