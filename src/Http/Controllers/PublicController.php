<?php

namespace Ry\Shop\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth, Mail, Session;
use App\User;
use Ry\Shop\Models\Sellable;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\OrderPayment;
use Ry\Profile\Models\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Ry\Shop\Models\OrderInvoice;
use Ry\Shop\Models\Cart;
use Ry\Shop\Models\Offer;

use Ry\Md\Recaptcha;
use Ry\Shop\Models\PackItem;

class PublicController extends Controller
{
	public function sell($sellable, $successIntent) {
		$sid = Sellable::where("sellable_id", "=", $sellable->id)->where("sellable_type", "=", get_class($sellable))->first();
		if(!$sid) {
			abort(404);
		}
		
		if($sid->sellable->price<=0)
			return "/login";
		
		return action("\Ry\Shop\Http\Controllers\PublicController@getCart") . "?sid=" . $sid->id;
	}
	
	public function getCart(Request $request) {
		$sellable = Sellable::where("id", "=", $request->get("sid"))->first();
		
		if($sellable)
			$sellable->addToCart();
		
		$cart = session("cart");
		$rows = [];
		foreach ($cart as $sid => $quantity) {
			$sellable = Sellable::where("id", "=", $sid)->first();
			$sellable->quantity = $quantity;
			$rows[] = $sellable;
		}
		return view("ryshop::cart", ["rows" => new Collection($rows)]);
	}
	
	public function postCart(Request $request) {
		
	}
	
	public function deleteCart(Request $request) {
		$cart = session("cart");
		unset($cart[$request->get("sid")]);
		session(["cart" => $cart]);
	}
	
    public function getPayment() {
    	if(!Session::has("url.intended"))
    		abort(503);
		return view("ryshop::payment", ["rows" => Sellable::all(), 
				"currency" => Sellable::currency(), 
				"intended" => Session::get("url.intended")]);
	}
	
	public function postMode(Request $request) {
		$ar = $request->all();
		return $this->presubmit($ar);
	}
	
	public function presubmit($ar) {
		$amounts = [0, 5000, 20000, 50000];
		$levels = ["Gratuite", "Granite", "Marbre", "Bois de rose"];
		$level = isset($ar["level"]) ? $ar["level"] : 0;;
		$phone = isset($ar["owner"]["phone"]) ? $ar["owner"]["phone"] : "";
		$payment = isset($ar["payment"]) ? $ar["payment"] : "";
		$target_ref = str_random(6);
		
		$carts = [];
		foreach($ar["carts"] as $cart) {
			if(isset($cart["quantity"]) && $cart["quantity"]>0) {
				$carts[] = [
						"sellable_id" => $cart["id"],
						"shop_id" => Shop::current()->id,
						"quantity" => $cart["quantity"]
				];
			}
		}
		if(count($carts)==0)
			abort(500);
		
		if(is_array($payment)) {
			$ar["currency"] = "USD";
			$amounts = [0, 5000/2000, 20000/2000, 50000/2000];
			if(Auth::guest()) {
				if(!isset($ar["owner"]["login"]) && isset($payment["payer"])) {
					$ar["owner"]["login"] = $payment["payer"]["payer_info"]["email"];
					//chercher l'user par son telephone
					$user = User::where("name", "LIKE", $ar["owner"]["login"])->first();
					if($user) {
						Auth::login($user);
	
						$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
								"amount" => $amounts[$level],
								"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
								"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
								"target" => [
										"id" => 1,
										"ref" => $target_ref
								],
								"currency" => $ar["currency"],
								"emitter" => [
										"id" => Auth::user()->id
								],
								"items" => [
										[
												"descriptif" => $ar["descriptif"],
												"quantity" => 1,
												"unit" => "unité",
												"pu" => $amounts[$level],
												"currency" => $ar["currency"]
										]
								]
						]);
	
						Mail::send("ryrealestate::emails.presubmit", [
								"descriptif" => $ar["descriptif"],
								"medias" => $ar["medias"],
								"user" => Auth::user(),
								"level" => $level,
								"phone" => $phone,
								"payment" => print_r($payment, true),
								"facture" => $facture->id
						], function($message){
							$message->subject("Nouvelle annonce");
							$message->to("contact@amelior.mg");
						});
							
						return [
								"redirect" => '/membre/factures/' . $facture->id
						];
					}
				}
				elseif(Auth::attempt([
						"email" => $ar["owner"]["login"],
						"password" => $ar["owner"]["password"]
				])) {
					$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
							"amount" => $amounts[$level],
							"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
							"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
							"target" => [
									"id" => 1,
									"ref" => $target_ref
							],
							"emitter" => [
									"id" => Auth::user()->id
							],
							"currency" => $ar["currency"],
							"items" => [
									[
											"descriptif" => $ar["descriptif"],
											"quantity" => 1,
											"unit" => "unité",
											"pu" => $amounts[$level],
											"currency" => $ar["currency"]
									]
							]
					]);
						
					Mail::send("ryrealestate::emails.presubmit", [
							"descriptif" => $ar["descriptif"],
							"medias" => $ar["medias"],
							"user" => Auth::user(),
							"level" => $level,
							"payment" => print_r($payment, true),
							"phone" => $phone,
							"facture" => $facture->id
					], function($message){
						$message->subject("Nouvelle annonce");
						$message->to("contact@amelior.mg");
					});
	
					return [
							"redirect" => '/membre/factures/' . $facture->id
					];
				}
					
				$tmp = new User();
				$tmp->name = $payment["payer"]["payer_info"]["first_name"] . " " . $payment["payer"]["payer_info"]["middle_name"]. " " . $payment["payer"]["payer_info"]["last_name"];
				$tmp->email = $payment["payer"]["payer_info"]["email"];
				$tmp->save();
				Auth::login($tmp);
	
				$adresse = app("\Ry\Geo\Http\Controllers\PublicController")->generate([
						"adresse" => [
								"raw" => $payment["payer"]["payer_info"]["shipping_address"]["line1"],
								"ville" => [
										"nom" => $payment["payer"]["payer_info"]["shipping_address"]["city"],
										"cp" => $payment["payer"]["payer_info"]["shipping_address"]["postal_code"],
										"country" => [
												"nom" => $payment["payer"]["payer_info"]["shipping_address"]["country_code"]
										]
								]
						]
				]);
	
				$tmp->profile()->create([
						"firstname" => $payment["payer"]["payer_info"]["first_name"],
						"lastname" => $payment["payer"]["payer_info"]["last_name"],
						"official" => $tmp->name,
						"adresse_id" => $adresse->id
				]);
	
				$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
						"amount" => $amounts[$level],
						"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
						"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
						"target" => [
								"id" => 1,
								"ref" => $target_ref
						],
						"emitter" => [
								"id" => Auth::user()->id
						],
						"currency" => $ar["currency"],
						"items" => [
								[
										"descriptif" => $ar["descriptif"],
										"quantity" => 1,
										"unit" => "unité",
										"pu" => $amounts[$level],
										"currency" => $ar["currency"]
								]
						]
				]);
					
				Mail::send("ryrealestate::emails.presubmit", [
						"descriptif" => $ar["descriptif"],
						"medias" => $ar["medias"],
						"user" => $tmp,
						"level" => $level,
						"payment" => print_r($payment, true),
						"phone" => "",
						"facture" => $facture->id
				], function($message){
					$message->subject("Nouvelle annonce");
					$message->to("contact@amelior.mg");
				});
				return [
						"redirect" => '/premium/factures/' . $facture->id
				];
			}
			else {
				$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
						"amount" => $amounts[$level],
						"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
						"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
						"target" => [
								"id" => 1,
								"ref" => $target_ref
						],
						"emitter" => [
								"id" => Auth::user()->id
						],
						"currency" => $ar["currency"],
						"items" => [
								[
										"descriptif" => $ar["descriptif"],
										"quantity" => 1,
										"unit" => "unité",
										"pu" => $amounts[$level],
										"currency" => $ar["currency"]
								]
						]
				]);
					
				Mail::send("ryrealestate::emails.presubmit", [
						"descriptif" => $ar["descriptif"],
						"medias" => $ar["medias"],
						"user" => Auth::user(),
						"level" => $level,
						"payment" => print_r($payment, true),
						"phone" => "",
						"facture" => $facture->id
				], function($message){
					$message->subject("Nouvelle annonce");
					$message->to("contact@amelior.mg");
				});
					
				return [
						"redirect" => '/membre/factures/' . $facture->id
				];
			}
		}
		else {
			if(Auth::guest()) {
				if(!isset($ar["owner"]["login"]) && isset($ar["owner"]["phone"])) {
					$ar["owner"]["login"] = $ar["owner"]["phone"];
					//chercher l'user par son telephone
					$phone = Phone::where("raw", "LIKE", preg_replace("/[^\d]/i", "", $ar["owner"]["phone"]))->first();
					if($phone) {
						$user = $phone->contact->owner;
						if($user) {
							Auth::login($user);
							
							$customer = app("\Ry\Shop\Http\Controllers\UserController")->customer([
									"currency" => $ar["currency"]
							]);
							Model::unguard();
							$cart = $customer->carts()->create([
									"shop_id" => Shop::current()->id,
									"currency_id" => $ar["currency"]["id"]
							]);
							$cart->items()->createMany($carts);
							
							$order = $cart->order()->create([
								"reference" => sprintf("%03s%06s", Shop::current()->id, $cart->id),
								"delivery_date" => date("Y-m-d H:i:s"),
								"invoice_date" => date("Y-m-d H:i:s"),
								"shop_id" => Shop::current()->id,
								"currency_id" => $cart->currency_id,
								"payment" => $payment,
								"valid" => true
							]);
							$invoice = $order->invoices()->create([
								"total_products" => $ar["amount"],
								"total_products_wt" => $ar["amount"],
								"total_wrapping_tax_incl" => $ar["amount"],
								"total_wrapping_tax_excl" => $ar["amount"]
							]);							
							$items = [];
							foreach($cart->items as $item) {
								$it = [
									"shop_id" => $cart->shop->id,
									"sellable_id" => $item->sellable->id,
									"sellable_name" => $item->sellable->sellable->title,
									"quantity" => $item->quantity,
									"price" => $item->sellable->sellable->price,
									"unit_price_tax_incl" => $item->sellable->sellable->price,
									"total_price_tax_incl" => $item->sellable->sellable->price * $item->quantity
								];
								$items[] = $it;
							}
							$order->items()->createMany($items);
							Model::reguard();
							Mail::send("ryshop::emails.paymentvalidation", [
									"invoice" => $invoice,
									"user" => Auth::user(),
									"cart" => $cart,
									"phone" => $phone
							], function($message){
								$message->subject("Demande de paiement");
								$message->to("contact@amelior.mg");
							});
	
							return [
									"redirect" => $invoice->detail_url
							];
						}
					}
				}
				elseif(Auth::attempt([
						"email" => $ar["owner"]["login"],
						"password" => $ar["owner"]["password"]
				])) {
					$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
							"amount" => $amounts[$level],
							"modalites" => $payment,
							"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
							"target" => [
									"id" => 1,
									"ref" => $target_ref
							],
							"emitter" => [
									"id" => Auth::user()->id
							],
							"currency" => $ar["currency"],
							"items" => [
									[
											"descriptif" => $ar["descriptif"],
											"quantity" => 1,
											"unit" => "unité",
											"pu" => $amounts[$level],
											"currency" => $ar["currency"]
									]
							]
					]);
						
					Mail::send("ryrealestate::emails.presubmit", [
							"descriptif" => $ar["descriptif"],
							"medias" => $ar["medias"],
							"user" => Auth::user(),
							"level" => $level,
							"payment" => $payment. " - " . $target_ref,
							"phone" => $phone,
							"facture" => $facture->id
					], function($message){
						$message->subject("Nouvelle annonce");
						$message->to("contact@amelior.mg");
					});
	
					return [
							"redirect" => '/membre/factures/' . $facture->id
					];
				}
					
				$tmp = new User();
				$tmp->name = $phone;
				$tmp->email = $phone . "@amelior.mg";
				$tmp->save();
				Auth::login($tmp);
				$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
						"amount" => $amounts[$level],
						"modalites" => $payment,
						"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
						"target" => [
								"id" => 1,
								"ref" => $target_ref
						],
						"currency" => $ar["currency"],
						"emitter" => [
								"id" => Auth::user()->id
						],
						"items" => [
								[
										"descriptif" => $ar["descriptif"],
										"quantity" => 1,
										"unit" => "unité",
										"pu" => $amounts[$level],
										"currency" => $ar["currency"]
								]
						]
				]);
					
				Mail::send("ryrealestate::emails.presubmit", [
						"descriptif" => $ar["descriptif"],
						"medias" => $ar["medias"],
						"user" => $tmp,
						"level" => $level,
						"payment" => $payment. " - " . $target_ref,
						"phone" => $phone,
						"facture" => $facture->id
				], function($message){
					$message->subject("Nouvelle annonce");
					$message->to("contact@amelior.mg");
				});
				return [
						"redirect" => '/premium/factures/' . $facture->id
				];
			}
			else {
				$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
						"amount" => $amounts[$level],
						"modalites" => $payment,
						"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
						"target" => [
								"id" => 1,
								"ref" => $target_ref
						],
						"currency" => $ar["currency"],
						"emitter" => [
								"id" => Auth::user()->id
						],
						"items" => [
								[
										"descriptif" => $ar["descriptif"],
										"quantity" => 1,
										"unit" => "unité",
										"pu" => $amounts[$level],
										"currency" => $ar["currency"]
								]
						]
				]);
					
				Mail::send("ryrealestate::emails.presubmit", [
						"descriptif" => $ar["descriptif"],
						"medias" => $ar["medias"],
						"user" => Auth::user(),
						"level" => $level,
						"payment" => $payment. " - " . $target_ref,
						"phone" => $phone,
						"facture" => $facture->id
				], function($message){
					$message->subject("Nouvelle annonce");
					$message->to("contact@amelior.mg");
				});
					
				return [
						"redirect" => '/membre/factures/' . $facture->id
				];
			}
		}
	
		return response()->json([
				"error" => "forbidden"
		], 403);
	}
	
	public function deleteCartItem(Request $request) {
		Cart::remove($request->all());
	}
	
	public function postPaypalCheckout(Request $request) {
		$ar = $request->all();
		
		if(Auth::guest()) {
			if(!isset($ar["owner"]["login"]) && isset($payment["payer"])) {
				$ar["owner"]["login"] = $payment["payer"]["payer_info"]["email"];
				//chercher l'user par son telephone
				$user = User::where("name", "LIKE", $ar["owner"]["login"])->first();
				if($user) {
					Auth::login($user);
		
					$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
							"amount" => $amounts[$level],
							"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
							"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
							"target" => [
									"id" => 1,
									"ref" => $target_ref
							],
							"currency" => $ar["currency"],
							"emitter" => [
									"id" => Auth::user()->id
							],
							"items" => [
									[
											"descriptif" => $ar["descriptif"],
											"quantity" => 1,
											"unit" => "unité",
											"pu" => $amounts[$level],
											"currency" => $ar["currency"]
									]
							]
					]);
		
					Mail::send("ryrealestate::emails.presubmit", [
							"descriptif" => $ar["descriptif"],
							"medias" => $ar["medias"],
							"user" => Auth::user(),
							"level" => $level,
							"phone" => $phone,
							"payment" => print_r($payment, true),
							"facture" => $facture->id
					], function($message){
						$message->subject("Nouvelle annonce");
						$message->to("contact@amelior.mg");
					});
						
					return [
							"redirect" => '/membre/factures/' . $facture->id
					];
				}
			}
			elseif(Auth::attempt([
					"email" => $ar["owner"]["login"],
					"password" => $ar["owner"]["password"]
			])) {
				$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
						"amount" => $amounts[$level],
						"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
						"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
						"target" => [
								"id" => 1,
								"ref" => $target_ref
						],
						"emitter" => [
								"id" => Auth::user()->id
						],
						"currency" => $ar["currency"],
						"items" => [
								[
										"descriptif" => $ar["descriptif"],
										"quantity" => 1,
										"unit" => "unité",
										"pu" => $amounts[$level],
										"currency" => $ar["currency"]
								]
						]
				]);
		
				Mail::send("ryrealestate::emails.presubmit", [
						"descriptif" => $ar["descriptif"],
						"medias" => $ar["medias"],
						"user" => Auth::user(),
						"level" => $level,
						"payment" => print_r($payment, true),
						"phone" => $phone,
						"facture" => $facture->id
				], function($message){
					$message->subject("Nouvelle annonce");
					$message->to("contact@amelior.mg");
				});
		
				return [
						"redirect" => '/membre/factures/' . $facture->id
				];
			}
				
			$tmp = new User();
			$tmp->name = $payment["payer"]["payer_info"]["first_name"] . " " . $payment["payer"]["payer_info"]["middle_name"]. " " . $payment["payer"]["payer_info"]["last_name"];
			$tmp->email = $payment["payer"]["payer_info"]["email"];
			$tmp->save();
			Auth::login($tmp);
		
			$adresse = app("\Ry\Geo\Http\Controllers\PublicController")->generate([
					"adresse" => [
							"raw" => $payment["payer"]["payer_info"]["shipping_address"]["line1"],
							"ville" => [
									"nom" => $payment["payer"]["payer_info"]["shipping_address"]["city"],
									"cp" => $payment["payer"]["payer_info"]["shipping_address"]["postal_code"],
									"country" => [
											"nom" => $payment["payer"]["payer_info"]["shipping_address"]["country_code"]
									]
							]
					]
			]);
		
			$tmp->profile()->create([
					"firstname" => $payment["payer"]["payer_info"]["first_name"],
					"lastname" => $payment["payer"]["payer_info"]["last_name"],
					"official" => $tmp->name,
					"adresse_id" => $adresse->id
			]);
		
			$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
					"amount" => $amounts[$level],
					"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
					"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
					"target" => [
							"id" => 1,
							"ref" => $target_ref
					],
					"emitter" => [
							"id" => Auth::user()->id
					],
					"currency" => $ar["currency"],
					"items" => [
							[
									"descriptif" => $ar["descriptif"],
									"quantity" => 1,
									"unit" => "unité",
									"pu" => $amounts[$level],
									"currency" => $ar["currency"]
							]
					]
			]);
				
			Mail::send("ryrealestate::emails.presubmit", [
					"descriptif" => $ar["descriptif"],
					"medias" => $ar["medias"],
					"user" => $tmp,
					"level" => $level,
					"payment" => print_r($payment, true),
					"phone" => "",
					"facture" => $facture->id
			], function($message){
				$message->subject("Nouvelle annonce");
				$message->to("contact@amelior.mg");
			});
			return [
					"redirect" => '/premium/factures/' . $facture->id
			];
		}
		else {
			$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
					"amount" => $amounts[$level],
					"modalites" => "paypal - " . $payment["id"] . " - " . $payment["state"],
					"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
					"target" => [
							"id" => 1,
							"ref" => $target_ref
					],
					"emitter" => [
							"id" => Auth::user()->id
					],
					"currency" => $ar["currency"],
					"items" => [
							[
									"descriptif" => $ar["descriptif"],
									"quantity" => 1,
									"unit" => "unité",
									"pu" => $amounts[$level],
									"currency" => $ar["currency"]
							]
					]
			]);
				
			Mail::send("ryrealestate::emails.presubmit", [
					"descriptif" => $ar["descriptif"],
					"medias" => $ar["medias"],
					"user" => Auth::user(),
					"level" => $level,
					"payment" => print_r($payment, true),
					"phone" => "",
					"facture" => $facture->id
			], function($message){
				$message->subject("Nouvelle annonce");
				$message->to("contact@amelior.mg");
			});
				
			return [
					"redirect" => '/membre/factures/' . $facture->id
			];
		}
	}
	
	public function postMemberCheckout() {
		$carts = Cart::session();
		if($carts->count()==0)
			abort(404);
		
		Recaptcha::check();
		
		$ar = $request->all();
		
		if(Auth::attempt([
				"email" => $ar["login"],
				"password" => $ar["password"]
		])) {
			$facture = app("\Ry\Cart\Http\Controllers\FactureController")->generate([
					"amount" => $amounts[$level],
					"modalites" => $payment,
					"actiondate" => isset($ar["actiondate"]) ? $ar["actiondate"] : date("Y-m-d H:i:s"),
					"target" => [
							"id" => 1,
							"ref" => $target_ref
					],
					"emitter" => [
							"id" => Auth::user()->id
					],
					"currency" => $ar["currency"],
					"items" => [
							[
									"descriptif" => $ar["descriptif"],
									"quantity" => 1,
									"unit" => "unité",
									"pu" => $amounts[$level],
									"currency" => $ar["currency"]
							]
					]
			]);
		
			Mail::send("ryrealestate::emails.presubmit", [
					"descriptif" => $ar["descriptif"],
					"medias" => $ar["medias"],
					"user" => Auth::user(),
					"level" => $level,
					"payment" => $payment. " - " . $target_ref,
					"phone" => $phone,
					"facture" => $facture->id
			], function($message){
				$message->subject("Nouvelle annonce");
				$message->to("contact@amelior.mg");
			});
		
			return [
					"redirect" => '/membre/factures/' . $facture->id
			];
		}
	}
	
	public function postMobileCheckout(Request $request) {	
		$carts = Cart::session();
		if($carts->count()==0)
			abort(404);
		
		if(Auth::guest())
			Recaptcha::check($request);
		
		$ar = $request->all();
		$payment_mode = isset($ar["payment_mode"]) ? $ar["payment_mode"] : "";
		$target_ref = str_random(6);
		
		if(Auth::guest()) {
			$ph = preg_replace("/[^\d]/i", "", $ar["phone"]);
			//chercher l'user par son telephone
			$phone = Phone::where("raw", "LIKE", $ph)->first();
			if($phone) {
				$user = $phone->contact->owner;
			}
			else {
				$user = new User();
				$user->name = $ph;
				$user->email = $ph . "@amelior.mg";
				$user->save();
				
				app("\Ry\Profile\Http\Controllers\AdminController")->putContacts($user, [[
						"contact_type" => "phone",
						"coord" => $ph
				]]);
			}
			Auth::login($user);
		}
		
		$customer = app("\Ry\Shop\Http\Controllers\UserController")->customer([
				"currency" => $ar["currency"]
		]);
		Model::unguard();
			
		$cart_items = [];
		foreach($carts as $cart) {
			if($cart->cart_type=="offers") {
				if($cart instanceof Offer) {
					$sellable = $cart->packs()->first()->items()->first();
				}
				
				$s = Sellable::where("sellable_id", "=", $sellable->id)->where("sellable_type", "=", get_class($sellable))->first();
				$cart_items[] = [
					"sellable_id" => $s->id,
					"shop_id" => Shop::current()->id,
					"quantity" => $request->get("items")[$cart->id]["cart_quantity"],
					"unit" => $cart->period == "mensuel" ? "mois" : null
				];
			}
		}
		
		$cart = $customer->carts()->create([
				"shop_id" => Shop::current()->id,
				"currency_id" => $ar["currency"]["id"]
		]);
		$cart->items()->createMany($cart_items);
		
		$order = $cart->order()->create([
				"reference" => sprintf("%03s%06s", Shop::current()->id, $cart->id),
				"delivery_date" => date("Y-m-d H:i:s"),
				"invoice_date" => date("Y-m-d H:i:s"),
				"shop_id" => Shop::current()->id,
				"currency_id" => $cart->currency_id,
				"payment" => $payment_mode,
				"valid" => true
		]);
		$invoice = $order->invoices()->create([
				"total_products" => $ar["amount"],
				"total_products_wt" => $ar["amount"],
				"total_wrapping_tax_incl" => $ar["amount"],
				"total_wrapping_tax_excl" => $ar["amount"]
		]);
		$items = [];
		foreach($cart->items as $item) {
			if($item->sellable->sellable instanceof PackItem) {
				$price = $item->sellable->sellable->pack->offer->price;
			}
			else {
				$price = $item->sellable->sellable->price;
			}
			
			$it = [
					"shop_id" => $cart->shop->id,
					"sellable_id" => $item->sellable->id,
					"sellable_name" => $item->sellable->sellable->title,
					"quantity" => $item->quantity,
					"price" => $price,
					"unit_price_tax_incl" => $price,
					"total_price_tax_incl" => $price * $item->quantity,
					"unit" => $item->unit
			];
			$items[] = $it;
		}
		$order->items()->createMany($items);
		Model::reguard();
		Mail::send("ryshop::emails.paymentrequest", [
				"invoice" => $invoice,
				"user" => Auth::user(),
				"cart" => $cart,
				"phone" => $ph
		], function($message){
			$message->subject("Demande de paiement");
			$message->to(Auth::user()->email);
			$message->from(env("contact", "manager@topmora.com"), env("COMPANY", "TOPMORA SHOP"));
		});
		
		return [
				"redirect" => $invoice->detail_url,
				"facture_id" => $invoice->id
		];
	}
}
