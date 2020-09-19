<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Order extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_orders";
    
    private static $subtotals = [];
    
    public function scopeAlpha($q) {
        $q->orderBy('ry_shop_orders.created_at', 'desc');
    }
    
    public function cart() {
    	return $this->belongsTo("Ry\Shop\Models\Cart", "cart_id");
    }
    
    public function shop() {
        return $this->belongsTo(Shop::class, "shop_id");
    }
    
    public function buyer() {
    	return $this->morphTo();
    }
    
    public function seller() {
        return $this->morphTo();
    }
    
    public function items() {
    	return $this->hasMany(OrderItem::class, "order_id");
    }
    
    public function invoices() {
    	return $this->hasMany("Ry\Shop\Models\OrderInvoice", "order_id");
    }
    
    public static function subtotal($year) {
        if(!isset(self::$subtotals[$year])) {
            $_order = static::where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)
            ->selectRaw("SUM(JSON_EXTRACT(ry_shop_orders.setup, '$.subtotal')) AS sum_subtotal")->first();
            if($_order)
                self::$subtotals[$year] = $_order;
            else
                self::$subtotals[$year] = (object)['sum_subtotal' => 0];
        }
        return self::$subtotals[$year];
    }
    
    public static function prettySubtotal($year) {
        $subtotal = self::subtotal($year);
        return app("centrale")->prettyCurrency($subtotal->sum_subtotal);
    }
    
    public static function quantityByMonth($year) {
        $results = static::groupBy(DB::raw("MONTH(ry_shop_orders.created_at)"))
            ->where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)
            ->selectRaw("COUNT(*) AS quantity, MONTH(ry_shop_orders.created_at) as month")->get();
        $ar = [];
        for($i=0; $i<12; $i++) {
            $ar[$i] = [
                'month' => $i+1,
                'quantity' => 0
            ];
        }
        foreach($results as $result) {
            $ar[$result->month-1] = $result;
        }
        return $ar;
    }
    
    public static function subtotalByMonth($year) {
        $results = static::groupBy(DB::raw("MONTH(ry_shop_orders.created_at)"))
        ->where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)
        ->selectRaw("SUM(JSON_EXTRACT(ry_shop_orders.setup, '$.subtotal')) AS quantity, MONTH(ry_shop_orders.created_at) as month")->get();
        $ar = [];
        for($i=0; $i<12; $i++) {
            $ar[$i] = [
                'month' => $i+1,
                'quantity' => 0
            ];
        }
        foreach($results as $j => $result) {
            $ar[$j] = $result;
        }
        return $ar;
    }
    
    public static function quantityOfYear($year) {
        return static::where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)->count();
    }
    
    public static function prettyTotalMonth($year) {
        $total = 0;
        $_order = static::where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)->where(DB::raw("MONTH(ry_shop_orders.created_at)"), "=", DB::raw("MONTH(CURRENT_DATE())"))->selectRaw("SUM(JSON_EXTRACT(ry_shop_orders.setup, '$.subtotal')) AS sum_subtotal")->first();
        if($_order) {
            $total = $_order->sum_subtotal;
        }
        return app("centrale")->prettyCurrency($total);
    }
    
    public static function subtotalByDay($year) {
        $rows = static::where(DB::raw("YEAR(ry_shop_orders.created_at)"), "=", $year)->where(DB::raw("MONTH(ry_shop_orders.created_at)"), "=", DB::raw("MONTH(CURRENT_DATE())"))
        ->groupBy(DB::raw("DATE(ry_shop_orders.created_at)"))
        ->orderBy("ry_shop_orders.created_at")
        ->selectRaw("SUM(JSON_EXTRACT(ry_shop_orders.setup, '$.subtotal')) AS quantity, DATE(ry_shop_orders.created_at) AS month")
        ->get();
        $start = Carbon::now()->year($year)->startOfMonth();
        $end = Carbon::now()->year($year)->endOfMonth();
        $ar = [];
        $dates = [];
        foreach($rows as $row) {
            $dates[$row->month] = $row->quantity;
        }
        while($start->lte($end)) {
            $ar[] = [
                "quantity" => isset($dates[$start->format("Y-m-d")]) ? $dates[$start->format("Y-m-d")] : 0,
                "month" => $start->format("Y-m-d")
            ];
            $start->addDay();
        }
        return $ar;
    }
    
    public static function prettyTurnover($year) {
        $total = static::subtotal($year);
        return app("centrale")->prettyCurrency($total->sum_subtotal);
    }
}
