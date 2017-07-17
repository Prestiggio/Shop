<?php 
namespace Ry\Shop;

use Ry\Shop\Models\Sellable;
use Braintree\Configuration;
use Braintree\ClientToken;

class Shop
{	
	public function sell($productClass) {
		$productClass::created([Sellable::class, "createdevent"]);
		$productClass::deleting([Sellable::class, "deletedevent"]);
	}
	
	public function js(&$setup) {
		Configuration::environment('sandbox');
		Configuration::merchantId('c8wwj5h4cqnqhzhz');
		Configuration::publicKey('7tqntjqppzt3fftj');
		Configuration::privateKey('4084ef84271168716a7363d8fe5df1e2');
		
		$setup["shop"] = [
				//"currency" => Sellable::currency(),
				"paypal" => [
						"bttoken" => ClientToken::generate()
				]
		];
	}
}

?>