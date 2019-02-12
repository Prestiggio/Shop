<?php

namespace Ry\Shop\Models\Bank;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = "ry_shop_bank_accounts";
    
    protected $fillable = ["setup", "bank_id", "currency_id"];
    
    protected $appends = ["nsetup"];
    
    public function bankable() {
        return $this->morphTo();
    }
    
    public function bank() {
        return $this->belongsTo(Bank::class, "bank_id");
    }
    
    public function getNsetupAttribute() {
        if($this->setup)
            return json_decode($this->setup, true);
        return [];
    }
    
    public function setNsetupAttribute($ar) {
        $this->setup = json_encode($ar);
    }
}
