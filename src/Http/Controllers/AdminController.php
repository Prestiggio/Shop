<?php
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Ry\Shop\Models\Cart;
use Ry\Shop\Models\Offer;
use Ry\Shop\Models\Pack;
use Ry\Shop\Models\PackItem;
use Ry\Shop\Models\OrderInvoice;
use Ry\Shop\Models\OrderPayment;
use Ry\Shop\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getSellables(Request $request) {
    	return view("ryshop::sellables", ["rows" => app("\Ry\Shop\Http\Controllers\AdminController")->getAjaxOffres($request)]);
    }
    
    public function getInvoice(Request $request) {
    	return view("ryshop::admin.invoice", ["rows" => OrderInvoice::all()]);
    }
    
    public function getCart(Request $request) {
    	return view("ryshop::cart", ["rows" => Cart::all()]);
    }
    
    public function getTest() {
    	return Cart::where("id", "=", 8)->first();
    }
    
    public function postCart(Request $request) {
    	Model::unguard();
    	
    	Model::reguard();
    }
    
    public function deleteCart(Request $request) {
    	
    }
    
    public function postInvoice(Request $request) {
    	$ar = $request->all();
    	
    	if(isset($ar["id"])) {
    		Model::unguard();
    		$invoice = OrderInvoice::where("id", "=", $ar["id"])->first();
    		if(doubleVal($ar["total_paid_tax_incl"])>0) {
	    		if($invoice->total_paid_tax_incl < doubleVal($ar["total_paid_tax_incl"])) {
	    			$payment = OrderPayment::create([
	    				"order_reference" => $invoice->order->reference,
	    				"currency_id" => $invoice->order->currency_id,
	    				"amount" => $ar["total_paid_tax_incl"],
	    				"payment_method" => "admin",
	    				"conversion_rate" => 1
	    			]);
	    			$invoice->payments()->create([
	    					"order_payment_id" => $payment->id
	    			]);
	    			foreach($invoice->order->items as $item) {
	    				$invoice->order->cart->customer->subscriptions()->create([
	    					"order_detail_id" => $item->id,
	    					"pack_item_id" => $item->sellable_id,
	    					"remainder" => $item->quantity,
	    					"expiry" => Carbon::now()->addMonth($item->quantity)
	    				]);
	    			}
	    		}
	    		$invoice->total_paid_tax_incl = doubleVal($ar["total_paid_tax_incl"]);
    		}
    		if(doubleVal($ar["total_paid_tax_excl"])>0) {
    			$invoice->total_paid_tax_excl = doubleVal($ar["total_paid_tax_excl"]);
    		}
    		$invoice->note = $ar["note"];
    		$invoice->save();
    		Model::reguard();
    	}
    }
    
    public function deleteInvoice(Request $request) {
    	 
    }
    
    public function getAjaxOffres(Request $request) {
    	return Offer::all();
    	return Offer::take(10)->get();
    }
    
    public function postSubmitOffer(Request $request) {
    	$user = auth()->user();
    	$ar = $request->all();
    	Model::unguard();
    	$data = [
    			"author_id" => $user->id,
    			"wpblog_url" => $ar["wpblog_url"],
    			"type" => $ar["type"],
    			"period" => isset($ar["period"]) ? $ar["period"] : null,
    			"price" => $ar["price"],
    			"multiple" => isset($ar["multiple"]) ? $ar["multiple"] : false,
    			"currency_id" => 1
    	];
    	$offer = false;
    	if(isset($ar["id"])) {
    		$offer = Offer::where("id", "=", $ar["id"])->first();
    	}
    
    	if(!$offer) {
    		$offer = Offer::create($data);
    	}
    	else {
    		$offer->update($data);
    	}
    	
    	foreach($ar["packs"] as $pack) {
    		if(isset($pack["deleted"])) {
    			if(isset($pack["id"]) && $pack["id"]>0) {
    				Pack::where("id", "=", $pack["id"])->delete();
    			}
    			continue;
    		}
    		
    		$p = false;
    		if(isset($pack["id"]) && $pack["id"]>0) {
    			$p = $offer->packs()->where("id", "=", $pack["id"])->first();
    		}
    		
    		if(!$p) {
    			$p = $offer->packs()->create([]);
    		}
    		
    		foreach($pack["items"] as $item) {
    			if(isset($item["deleted"])) {
    				if(isset($item["id"]) && $item["id"]>0) {
    					PackItem::where("id", "=", $item["id"])->delete();
    				}
    				continue;
    			}
    				
    			$data = [
    					"quantity" => $item["quantity"],
    					"vendible_type" => $item["vendible_type"]
    			];
    				
    			$pa = false;
    			if(isset($item["id"]) && $item["id"]>0) {
    				$pa = $p->items()->where("id", "=", $item["id"])->first();
    			}
    				
    			if(!$pa) {
    				$pa = $p->items()->create($data);
    			}
    			else {
    				$pa->update($data);
    			}
    		}
    	}
    	
    	Model::reguard();
    	
    	return $offer;
    }
    
    public function postDeleteOffer(Request $request) {
    	Offer::where("id", "=", $request->get("id"))->delete();
    }
}
