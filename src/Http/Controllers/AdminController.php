<?php
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Ry\Shop\Models\Cart;
use Ry\Shop\Models\OrderInvoice;
use Illuminate\Database\Eloquent\Model;

class AdminController extends Controller
{
    public function getSellables() {
    	return view("ryshop::sellables");
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
    	
    	if(isset($ar["id"]) && doubleVal($ar["total_paid_tax_incl"])>0) {
    		Model::unguard();
    		$invoice = OrderInvoice::where("id", "=", $ar["id"])->first();
    		if($invoice->total_paid_tax_incl < doubleVal($ar["total_paid_tax_incl"])) {
    			//$invoice->payments()
    		}
    		$invoice->total_paid_tax_incl = doubleVal($ar["total_paid_tax_incl"]);
    		$invoice->save();
    		Model::reguard();
    	}
    }
    
    public function deleteInvoice(Request $request) {
    	 
    }
}
