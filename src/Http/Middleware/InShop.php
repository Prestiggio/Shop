<?php

namespace Ry\Shop\Http\Middleware;

use Closure;
use Ry\Shop\Models\ShopGroup;
use Ry\Shop\Models\Shop;
use Illuminate\Database\Eloquent\Model;
use Auth;

class InShop
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	$shop = Shop::where("id", "=", env("SHOP", 1))->first();
    	if(!$shop) {
    		$shopgroup = ShopGroup::where("id", "=", 1)->first();
    		if(!$shopgroup) {
				ShopGroup::unguard();
    			$shopgroup = ShopGroup::create([
    					"name" => "Topmora Group",
    					"share_customer" => true,
    					"share_order" => true,
    					"share_stock" => true,
    					"active" => true
				]);
				ShopGroup::reguard();
			}
			Shop::unguard();
    		$shopgroup->shops()->create([
    				"name" => "Topmora Central Shop",
    				"owner_id" => Auth::user()->id,
    				"active" => true
    		]);
    		Shop::reguard();
    	}
    	Shop::setCurrent($shop);
    	
        return $next($request);
    }
}
