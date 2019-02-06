<?php

namespace Ry\Shop\Models\Bank;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = "ry_shop_bank_accounts";
    
    protected $fillable = ["setup", "bank_id", "currency_id"];
    
    public function bankable() {
        return $this->morphTo();
    }
    
    public function bank() {
        return $this->belongsTo(Bank::class, "bank_id");
    }
}
