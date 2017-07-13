<?php

namespace Ry\Shop\Http\Middleware;

use Closure;
use Ry\Shop\Models\ShopGroup;
use Ry\Shop\Models\Shop;
use Illuminate\Database\Eloquent\Model;

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
    		Model::unguard();
    		$shopgroup = ShopGroup::where("id", "=", 1)->first();
    		if(!$shopgroup) {
    			$shopgroup = ShopGroup::create([
    					"name" => "Topmora Group",
    					"share_customer" => true,
    					"share_order" => true,
    					"share_stock" => true,
    					"active" => true
    			]);
    		}
    		$shopgroup->shops()->create([
    				"name" => "Topmora Central Shop"
    		]);
    		Model::reguard();
    	}
    	Shop::setCurrent($shop);
    	
        return $next($request);
    }
}
