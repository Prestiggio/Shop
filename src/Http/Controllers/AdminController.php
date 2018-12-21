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
use Ry\Shop\Models\Subscription;
use Ry\Shop\Models\OrderInvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Ry\Shop\Models\Shop;
use Mail;

class AdminController extends Controller
{
	public function __construct() {
		$this->middleware("inshop", [
			'only' => ['postInvoice']
		]);
	}

    public function getSellables(Request $request) {
    	return view("ryshop::sellables", ["rows" => app("\Ry\Shop\Http\Controllers\AdminController")->getAjaxOffres($request)]);
    }
    
    public function getInvoice(Request $request) {
    	return view("ryshop::admin.invoice", ["rows" => OrderInvoice::with("payments", "order.cart.customer.facturable")->orderBy("id", "DESC")->get()]);
    }
    
    public function getCart(Request $request) {
    	return view("ryshop::cart", ["rows" => Cart::all()]);
    }
    
    public function postCart(Request $request) {
    	
    }
    
    public function deleteCart(Request $request) {
    	
    }
    
    public function postInvoice(Request $request) {
    	$ar = $request->all();
    	
    	if(isset($ar["id"])) {
    		$invoice = OrderInvoice::where("id", "=", $ar["id"])->first();
    		
    		$amount = 0;
			$receipt = false;
			OrderInvoicePayment::unguard();
    		foreach($ar["payments"] as $payment) {
    			if(isset($payment["id"]) && $payment["id"]>0) {
    				$_payment = $invoice->payments()->where("ry_shop_order_payments.id", "=", $payment["id"])->first();
    				if($_payment) {
    					if(isset($payment["deleted"]) && $payment["deleted"]) {
    						$amount -= $payment->amount;
    						$payment->delete();
    					}
    					else {
    						unset($payment["id"]);
    						unset($payment["pivot"]);
    						$_payment->update($payment);
    					}
    				}
    			}
    			else if($payment["amount"]>0) {
    				$payment["currency_id"] = $invoice->order->currency_id;
    				$payment["order_reference"] = "P" . ($invoice->payments->count() + 1) . $invoice->order->reference;
    				$payment["conversion_rate"] = 1;
    				$_payment = $invoice->payments()->create($payment);
    				$receipt = true;
    			}
    			$amount+=doubleval($payment["amount"]);
			}
			OrderInvoicePayment::reguard();
    		if($invoice->total_wrapping_tax_incl <= $amount) {
				Subscription::unguard();
    			foreach($invoice->order->items as $item) {
    				$invoice->order->cart->customer->subscriptions()->create([
    					"order_detail_id" => $item->id,
    					"pack_item_id" => $item->sellable_id,
    					"remainder" => $item->quantity,
    					"expiry" => Carbon::now()->addMonth($item->quantity)
    				]);
				}
				Subscription::reguard();
    		}
	    	$invoice->total_paid_tax_incl = $amount;
	    	$invoice->total_paid_tax_excl = $amount;
    		$invoice->note = $ar["note"];
    		$invoice->save();
    		
    		if($receipt) {
    			$user = $invoice->order->cart->customer->owner;
    			if($invoice->total_wrapping_tax_incl <= $amount)
    				$template = "ryshop::emails.subscribed";
    			else 
    				$template = "ryshop::emails.receipt";
    			
    			Mail::send($template, [
    					"user" => $user,
    					"invoice" => $invoice,
						"payment" => $_payment,
						"shop" => Shop::current()
    			], function($message) use ($_payment, $user){
    				$message->subject(env("SHOP", "TOPMORA SHOP")." - Facture " . $_payment->order_reference);
    				$message->to($user->email, $user->companies()->first()->nom);
    				$message->from(env("contact", "manager@topmora.com"), env("SHOP", "TOPMORA SHOP"));
    			});
    		}
    	}
    }
    
    public function deleteInvoice(Request $request) {
		OrderInvoice::where("id", "=", $request->get("id"))->delete();
    }
    
    public function getAjaxOffres(Request $request) {
    	return Offer::all();
    	return Offer::take(10)->get();
    }
    
    public function postSubmitOffer(Request $request) {
    	$user = auth()->user();
    	$ar = $request->all();
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
	
		Offer::unguard();
    	if(!$offer) {
    		$offer = Offer::create($data);
    	}
    	else {
    		$offer->update($data);
		}
		Offer::reguard();
    	
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
				Pack::unguard();
				$p = $offer->packs()->create([]);
				Pack::reguard();
    		}
			
			PackItem::unguard();
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
			PackItem::reguard();
    	}    	
    	return $offer;
    }
    
    public function postDeleteOffer(Request $request) {
    	Offer::where("id", "=", $request->get("id"))->delete();
    }
    
    public function controller_action($action, Request $request) {
        $method_name = $request->getMethod() . camel_case($action);
        return $this->$method_name($request);
    }
}
