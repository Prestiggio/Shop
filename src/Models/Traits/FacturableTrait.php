<?php 
namespace Ry\Shop\Models\Traits;

use Session;
use Illuminate\Http\Request;

trait FacturableTrait
{
	public function customerAccount() {
		return $this->morphOne("Ry\Shop\Models\Customer", "facturable");
	}
	
	public function hasEnoughMoney(Request $request) {
		return false;
	}
	
	public function isCustomer() {
		return $this->customer_account != null;
	}
}
?>