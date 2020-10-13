<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Ry\Analytics\Models\Traits\LinkableTrait;
use Ry\Admin\Models\Traits\HasJsonSetup;

class OrderInvoice extends Model
{
	use LinkableTrait, HasJsonSetup;
	
    protected $table = "ry_shop_order_invoices";
    
    //protected $with = ["payments"];
    
    protected static function boot() {
        parent::boot();
        
        static::addGlobalScope('chrono', function($q){
            $q->orderBy('created_at', 'desc');
        });
    }
    
    public function order() {
    	return $this->belongsTo("Ry\Shop\Models\Order", "order_id");
    }
    
    public function getDetailUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\UserController@invoiceDetail", ["invoice" => $this]);
    }
    
    public function getSlugAttribute() {
    	if($this->slugs()->exists())
    		return $this->slugs->slug;
    	
    	return str_random(16);
    }
    
    public function getAdminUrlAttribute() {
    	return action("\Ry\Shop\Http\Controllers\AdminController@getInvoice") . "?id=" . $this->id;
    }
    
    public function payments() {
    	return $this->belongsToMany(Payment::class, "ry_shop_order_invoice_payments", "order_invoice_id", "order_payment_id");
    }
    
    public function buyer() {
        return $this->morphTo();
    }
    
    public function seller() {
        return $this->morphTo();
    }
    
    public function getBuyerUrlAttribute() {
        $site = app("centrale")->getSite();
        return (isset($site->nsetup['ssl']) ? 'https://' : 'http://') . $site->nsetup['subdomains']['affiliate'] . __("/marketplace/invoice?id=:id", ["id" => $this->id]);
    }
    
    public function getSellerUrlAttribute() {
        $site = app("centrale")->getSite();
        return (isset($site->nsetup['ssl']) ? 'https://' : 'http://') . $site->nsetup['subdomains']['supplier'] . __("/marketplace/invoice?id=:id", ["id" => $this->id]);
    }
}
