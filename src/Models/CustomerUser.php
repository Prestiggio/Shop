<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Ry\Admin\Models\Traits\HasJsonSetup;

class CustomerUser extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_customer_users";
    
    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
    
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
