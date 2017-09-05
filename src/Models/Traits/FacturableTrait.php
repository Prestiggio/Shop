<?php 
namespace Ry\Shop\Models\Traits;

use Session;
use Illuminate\Http\Request;

trait FacturableTrait
{
	public function customerAccount() {
		return $this->morphOne("Ry\Shop\Models\Customer", "facturable");
	}
	
	public function hasEnoughMoney(Request $request=null) {
		return $this->customerAccount->subscriptions->count() > 0;
	}
	
	public function isCustomer() {
		return $this->customerAccount != null;
	}
}
?>