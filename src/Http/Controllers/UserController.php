<?php
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Ry\Shop\Models\Cart;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\Sellable;
use Auth;
use Illuminate\Database\Eloquent\Model;

class UserController extends Controller
{
    public function getHistoric(Request $request) {
		return view("ryshop::historic");
	}
	
	public function customer($ar) {
		$user = Auth::user();
		
		Model::unguard();
		
		if(!$user->customerAccount) {
			$user->customerAccount = $user->customerAccount()->create([
				"shop_id" => Shop::current()->id,
				"currency_id" => isset($ar["currency"]) ? $ar["currency"]["id"] : Sellable::currency()->id,
				"show_public_prices" => true,
				"active" => isset($ar["user"]),
				"is_guest" => !isset($ar["user"])
			]);
		}
		
		Model::reguard();
		
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
