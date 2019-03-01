<?php

namespace Ry\Shop\Models\Bank;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;

class BankAccount extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_bank_accounts";
    
    protected $fillable = ["setup", "bank_id", "currency_id"];
    
    protected $appends = ["nsetup"];
    
    public function bankable() {
        return $this->morphTo();
    }
    
    public function bank() {
        return $this->belongsTo(Bank::class, "bank_id");
    }
}
