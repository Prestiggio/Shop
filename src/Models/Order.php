<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Facades\DB;
use Ry\Centrale\SiteScope;
use Carbon\Carbon;

class Order extends Model
{
    use HasJsonSetup;
    
    protected $table = "ry_shop_orders";
    
    private static $subtotals = [];
    
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
        if(!isset(self::$subtotals[$year]))
            self::$subtotals[$year] = DB::selectOne("SELECT SUM(setup->'$.subtotal') AS sum_subtotal 
            FROM `ry_shop_orders` 
            WHERE YEAR(created_at) = :year", ['year' => $year]);
        return self::$subtotals[$year];
    }
    
    public static function prettySubtotal($year) {
        $subtotal = self::subtotal($year);
        return app("centrale")->prettyCurrency($subtotal->sum_subtotal);
    }
    
    public static function quantityByMonth($year) {
        return static::groupBy(DB::raw("MONTH(created_at)"))
            ->whereRaw("YEAR(created_at) = :year", ["year" => $year])
            ->selectRaw("COUNT(*) AS quantity, MONTH(created_at) as month")->get();
    }
    
    public static function subtotalByMonth($year) {
        return static::groupBy(DB::raw("MONTH(created_at)"))
        ->whereRaw("YEAR(created_at) = :year", ["year" => $year])
        ->selectRaw("SUM(setup->'$.subtotal') AS quantity, MONTH(created_at) as month")->get();
    }
    
    public static function quantityOfYear($year) {
        return static::whereRaw("YEAR(created_at) = :year", ["year" => $year])->count();
    }
    
    public static function prettyTotalMonth($year) {
        $total = static::whereRaw("YEAR(created_at) = :year AND MONTH(created_at) = MONTH(CURRENT_DATE())", ["year" => $year])->selectRaw("SUM(setup->'$.subtotal') AS sum_subtotal")->first()->sum_subtotal;
        return app("centrale")->prettyCurrency($total);
    }
    
    public static function subtotalByDay($year) {
        $rows = static::whereRaw("YEAR(created_at) = :year AND MONTH(created_at) = MONTH(CURRENT_DATE())", ["year" => $year])
        ->groupBy(DB::raw("DATE(created_at)"))
        ->orderBy("created_at")
        ->selectRaw("SUM(setup->'$.subtotal') AS quantity, DATE(created_at) AS month")
        ->get();
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
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
        $total = static::whereRaw("YEAR(created_at) = :year", ["year" => $year])->selectRaw("SUM(setup->'$.subtotal') AS sum_subtotal")->first()->sum_subtotal;
        return app("centrale")->prettyCurrency($total);
    }
}
