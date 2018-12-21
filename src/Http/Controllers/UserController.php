<?php
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Ry\Shop\Models\Cart;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\Sellable;
use Ry\Shop\Models\Customer;
use Auth;
use Illuminate\Database\Eloquent\Model;

class UserController extends Controller
{
    public function getHistoric(Request $request) {
		return view("ryshop::historic");
	}
	
	public function controller_action($action, Request $request) {
	    $method_name = $request->getMethod() . camel_case($action);
	    return $this->$method_name($request);
	}
	
	public function customer($ar=[]) {
		$user = Auth::user();
		if(!$user->customerAccount) {
			Customer::unguard();
			$user->customerAccount = $user->customerAccount()->create([
				"shop_id" => Shop::current()->id,
				"currency_id" => isset($ar["id"]) ? $ar["id"] : Sellable::currency()->id,
				"show_public_prices" => true,
				"active" => isset($ar["active"]) ? $ar["active"] : false,
				"is_guest" => isset($ar["is_guest"]) ? $ar["is_guest"] : true
			]);
			Customer::reguard();
		}		
		return $user->customerAccount;
	}
	
	public function cart($ar) {
		
	}
	
	public function invoiceDetail($invoice) {
		return view("ryshop::invoice", ["row" => $invoice]);
	}
	
	public function download($invoice) {
		if($invoice->order->cart->customer->owner->id == Auth::user()->id) {
			$pdf = new \HTML2PDF('P', 'A4', 'fr');
			$pdf->pdf->SetAuthor('Kipa');
			$pdf->pdf->SetTitle('Facture ' . $invoice->order->reference);
			$pdf->pdf->SetSubject("Facture");
			$pdf->setDefaultFont("Arial");
			$pdf->writeHTML(view("ryshop::pdf", ["row" => $invoice])->render());
			$pdf->Output("facture-".date("Y-m-d-Hh-imn").".pdf", "D");
		}
	}
}
