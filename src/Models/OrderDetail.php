<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use NumberFormatter;
use Carbon\Carbon;

class OrderDetail extends Model
{
    protected $table = "ry_shop_order_details";
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function shop() {
    	return $this->belongsTo("Ry\Shop\Models\Shop", "shop_id");
    }
    
    public function sellable() {
    	return $this->belongsTo("Ry\Shop\Models\Sellable", "sellable_id");
    }

    public function getLtrAttribute() {
        $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        return $f->format($this->unit_price_tax_incl);
    }

    public function getDebitStartAttribute() {
        setlocale(LC_TIME, 'French');
        Carbon::setLocale('fr');
        Carbon::setUtf8(true);
        $startDate = $this->created_at;
        if($startDate->day>15) {
            return Carbon::parse("first day of next month")->formatLocalized("%B %Y");
        }
        return $startDate->formatLocalized("%B %Y");
    }

    public function getDebitEndAttribute() {
        setlocale(LC_TIME, 'French');
        Carbon::setLocale('fr');
        Carbon::setUtf8(true);
        $startDate = $this->created_at->addMonth($this->quantity);
        if($startDate->day>15) {
            return Carbon::parse("first day of next month")->addMonth($this->quantity)->formatLocalized("%B %Y");
        }
        return $startDate->formatLocalized("%B %Y");
    }
    
}
