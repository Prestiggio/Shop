<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Admin\Models\Traits\HasJsonSetup;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Cache;
use Ry\Affiliate\Models\Affiliate;
use Ry\Centrale\Models\Site;

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
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        return isset($stats['period_categories'][$year]['sum_supplier_amount']) ? $stats['period_categories'][$year]['sum_supplier_amount'] : 0;
    }
    
    public static function prettySubtotal($year) {
        $subtotal = self::subtotal($year);
        return app("centrale")->prettyCurrency($subtotal);
    }
    
    public static function quantityByMonth($year) {
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        $ar = [];
        for($i=0; $i<12; $i++) {
            $ar[$i] = [
                'month' => $i+1,
                'quantity' => 0
            ];
        }
        if(isset($stats['period_categories'][$year]['children'])) {
            foreach($stats['period_categories'][$year]['children'] as $month => $result) {
                $ar[$month-1]['quantity'] = $result['n'];
            }
        }
        return $ar;
    }
    
    public static function subtotalByMonth($year) {
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        $ar = [];
        for($i=0; $i<12; $i++) {
            $ar[$i] = [
                'month' => $i+1,
                'quantity' => 0
            ];
        }
        if(isset($stats['period_categories'][$year]['children'])) {
            foreach($stats['period_categories'][$year]['children'] as $month => $result) {
                $ar[$month-1]['quantity'] = $result['sum_supplier_amount'];
            }
        }
        return $ar;
    }
    
    public static function quantityOfYear($year) {
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        if(isset($stats['period_categories'][$year]['n']))
            return $stats['period_categories'][$year]['n'];
        return 0;
    }
    
    public static function prettyTotalMonth($year) {
        $total = 0;
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        $month = Carbon::now()->month;
        if(isset($stats['period_categories'][$year]['children'][$month])) {
            $total = $stats['period_categories'][$year]['children'][$month]['sum_supplier_amount'];
        }
        return app("centrale")->prettyCurrency($total);
    }
    
    public static function subtotalByDay($year) {
        $site = app("centrale")->getSite();
        $stats = Cache::tags('stats')->get('manager'.$site->id);
        $start = Carbon::now()->year($year)->startOfMonth();
        $end = Carbon::now()->year($year)->endOfMonth();
        $ar = [];
        while($start->lte($end)) {
            $ar[$start->format("Y-m-d")] = [
                "quantity" => 0,
                "month" => $start->format("Y-m-d")
            ];
            $start->addDay();
        }
        if(isset($stats['period_categories'][$year]['children'][Carbon::now()->month]['children'])) {
            $rows = $stats['period_categories'][$year]['children'][Carbon::now()->month]['children'];
            foreach($rows as $day => $row) {
                $ar[Carbon::now()->day($day)->format('Y-m-d')]['quantity'] = $row['sum_supplier_amount'];
            }
        }
        return array_values($ar);
    }
    
    public static function prettyTurnover($year) {
        $total = static::subtotal($year);
        return app("centrale")->prettyCurrency($total);
    }
    
    public function pdf($mode = 'D') {
        $this->append('nsetup');
        $this->items->map(function($order_item){
            $order_item->append('nsetup');
            $order_item->sellable->append('nsetup');
            $order_item->sellable->append('visible_specs');
        });
        $this->shop->owner->append('complete_contacts');
        $this->setAttribute('currency', $this->cart ? $this->cart->currency : app("centrale")->getCurrency());
        $formatter = new \NumberFormatter('fr-FR', \NumberFormatter::DECIMAL);
        $currency_formatter = new \NumberFormatter('fr-FR', \NumberFormatter::CURRENCY);
        $pdf = new Mpdf([
            'debug' => env('APP_DEBUG'),
            'defaultCssFile' => public_path('css/pdf.css'),
            'tempDir' => storage_path('tmp')
        ]);
        $pdf->SetAuthor('Centrale');
        $pdf->SetTitle('Commande ' . $this->nsetup['serial']);
        $pdf->SetSubject("Commande");
        $pdf->setDefaultFont("Arial");
        $pdf->writeHTML(view("ryshop::buyer.pdf", [
            "row" => $this,
            "f" => $formatter,
            "f2" => $currency_formatter,
            "vat" => app("centrale")->getVat()
        ])->render());
        return $pdf->Output(__("commande-:code.pdf", ['code' => $this->nsetup['serial']]), $mode);
    }
    
    public function sellerPdf($mode = 'D') {
        $this->append('nsetup');
        $this->items->map(function($order_item){
            $order_item->append('nsetup');
            $order_item->sellable->append('nsetup');
            $order_item->sellable->append('visible_specs');
        });
        $this->shop->owner->append('complete_contacts');
        $this->setAttribute('currency', $this->cart ? $this->cart->currency : app("centrale")->getCurrency());
        $formatter = new \NumberFormatter('fr-FR', \NumberFormatter::DECIMAL);
        $currency_formatter = new \NumberFormatter('fr-FR', \NumberFormatter::CURRENCY);
        $pdf = new Mpdf([
            'debug' => env('APP_DEBUG'),
            'defaultCssFile' => public_path('css/pdf.css'),
            'tempDir' => storage_path('tmp')
        ]);
        $pdf->SetAuthor('Centrale');
        $pdf->SetTitle('Facture ' . $this->nsetup['serial']);
        $pdf->SetSubject("Facture");
        $pdf->setDefaultFont("Arial");
        $pdf->writeHTML(view("ryshop::seller.pdf", [
            "row" => $this,
            "f" => $formatter,
            "f2" => $currency_formatter,
            "vat" => app("centrale")->getVat()
        ])->render());
        return $pdf->Output(__("commande-:code.pdf", ['code' => $this->nsetup['serial']]), $mode);
    }
    
    public function getBuyerUrlAttribute() {
        return app("centrale")->buildBuyerUrl(__("/marketplace/orders?cart_id=:cart_id", ['cart_id' => $this->cart_id]));
    }
    
    public function getSellerUrlAttribute() {
        return app("centrale")->buildSellerUrl(__("/marketplace/orders?cart_id=:cart_id", ['cart_id' => $this->cart_id]));
    }
}
