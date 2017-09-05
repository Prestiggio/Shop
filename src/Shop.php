<?php 
namespace Ry\Shop;

use Ry\Shop\Models\Sellable;
use Braintree\Configuration;
use Braintree\ClientToken;
use Schema;

class Shop
{
	public function sell($sellableClass) {
		$sellableClass::created([Sellable::class, "createdevent"]);
		$sellableClass::deleting([Sellable::class, "deletingevent"]);
	}
	
	public function js(&$setup) {
		Configuration::environment('sandbox');
		Configuration::merchantId('c8wwj5h4cqnqhzhz');
		Configuration::publicKey('7tqntjqppzt3fftj');
		Configuration::privateKey('4084ef84271168716a7363d8fe5df1e2');
		
		$setup["shop"] = [
				"currency" => Schema::hasTable("ry_shop_sellables") ? Sellable::currency() : null,
				"paypal" => [
						"bttoken" => ClientToken::generate()
				]
		];
	}
}

?>