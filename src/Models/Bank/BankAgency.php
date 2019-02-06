<?php namespace Ry\Shop\Models\Bank;

use Illuminate\Database\Eloquent\Model;
use Ry\Geo\Models\Traits\Geoable;

class BankAgency extends Model {
    use Geoable;

    protected $table = "ry_shop_bank_agencies";

    public function bank() {
        return $this->belongsTo(Bank::class);
    }
}
