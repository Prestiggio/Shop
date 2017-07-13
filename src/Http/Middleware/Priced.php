<?php

namespace Ry\Shop\Http\Middleware;

use Closure, Auth;

class Priced
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
    	$user = Auth::user();
    	
    	if(!$user || ($user && !$user->hasEnoughMoney($request))) {
    		return redirect()->guest(action("\Ry\Shop\Http\Controllers\PublicController@getPayment"));
    	}
    	
        return $next($request);
    }
}
