<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{   
    protected $table = "ry_shop_currencies";
    
    protected $fillable = ["name", "iso_code", "symbol", "conversion_rate"];
}
