<?php

namespace Ry\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Offer extends Model
{
	protected $table = "ry_shop_offers";
	 
	private $post, $amount, $cart_quantity;
	
	protected $appends = ["title", "content", "dprice", "add2cart", "cart_title", "cart_quantity", "cart_amount", "cart_unitprice", "cart_type"];
	
	protected $with = ["packs"];
	
	public function author() {
		return $this->belongsTo("App\User", "author_id");
	}
	
	public function packs() {
		return $this->hasMany("Ry\Shop\Models\Pack", "offer_id");
	}
	
	public function currency() {
		return $this->belongsTo("Ry\Shop\Models\Currency", "currency_id");
	}
	
	public function getTitleAttribute() {
		if(!$this->post)
			$this->post = app("rywpblog")->post($this->wpblog_url);
		return $this->post->post_title;
	}
	
	public function getContentAttribute() {
		if(!$this->post)
			$this->post = app("rywpblog")->post($this->wpblog_url);
		return $this->post->post_content;
	}
	
	public function getActiveAttribute() {
		if(!Auth::guest() && $this->price==0) {
			return false;
		}
		return true;
	}
	
	public function getPayedUrlAttribute() {
		return "#!/add2cart/offers/" . $this->id;
	}
	
	public function getDpriceAttribute() {
		if($this->price<=0)
			return trans("ryshop::overall.free");
		
		return number_format($this->price, 2, ",", ".") . " " . $this->currency->iso_code;
	}
	
	public function getAdd2cartAttribute() {
		if($this->price<=0)
			return trans("rysocin::auth.login");
		 
		return trans("ryshop::overall.cart.add");
	}
	
	public function getCartUnitpriceAttribute() {
		return $this->price;
	}
	
	public function getCartTitleAttribute() {
		return $this->title;
	}
	
	public function setCartQuantityAttribute($quantity) {
		$this->cart_quantity = $quantity;
		$this->amount = $this->cart_unitprice * $this->cart_quantity;
	}
	
	public function getCartAmountAttribute() {
		return $this->amount;
	}
	
	public function getCartQuantityAttribute() {
		return $this->cart_quantity;
	}
	
	public function getCartTypeAttribute() {
		return "offers";
	}
}
