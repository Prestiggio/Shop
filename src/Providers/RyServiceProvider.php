<?php

namespace Ry\Shop\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Ry\Shop\Shop;
use Ry\Shop\Console\Commands\ShopSetup;
use Ry\Shop\Models\OrderInvoice;
use Ry\Shop\Models\Customer;
use Ry\Shop\Models\Pack;
use Ry\Shop\Models\PackItem;
use Ry\Shop\Console\Commands\ExpiredReminder;
use Ry\Shop\Models\CartSellable;
use Ry\Shop\Models\OrderItem;

class RyServiceProvider extends ServiceProvider
{
	/**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	/*
    	$this->publishes([    			
    			__DIR__.'/../config/ryshop.php' => config_path('ryshop.php')
    	], "config");  
    	$this->mergeConfigFrom(
	        	__DIR__.'/../config/ryshop.php', 'ryshop'
	    );
    	$this->publishes([
    			__DIR__.'/../assets' => public_path('vendor/ryshop'),
    	], "public");    	
    	*/
    	//ressources
    	$this->loadViewsFrom(__DIR__.'/../ressources/views', 'ryshop');
    	$this->loadTranslationsFrom(__DIR__.'/../ressources/lang', 'ryshop');
    	/*
    	$this->publishes([
    			__DIR__.'/../ressources/views' => resource_path('views/vendor/ryshop'),
    			__DIR__.'/../ressources/lang' => resource_path('lang/vendor/ryshop'),
    	], "ressources");
    	*/
    	$this->publishes([
    			__DIR__.'/../database/factories/' => database_path('factories'),
	        	__DIR__.'/../database/migrations/' => database_path('migrations')
	    ], 'migrations');
    	$this->map();
    	//$kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
    	//$kernel->pushMiddleware('Ry\Facebook\Http\Middleware\Facebook');
    	
    	$this->app["router"]->middleware('priced', 'Ry\Shop\Http\Middleware\Priced');
    	$this->app["router"]->middleware('inshop', 'Ry\Shop\Http\Middleware\InShop');
    	
		app("ryanalytics.slug")->register("invoice", OrderInvoice::class);
		app("ryanalytics.slug")->register("customer", Customer::class);
    	
    	app("ryshop")->sell(PackItem::class);
    	
    	$this->app->booted(function(){
            $sellable_types = CartSellable::selectRaw('DISTINCT sellable_type')->get();
            foreach($sellable_types as $sellable_type) {
                call_user_func_array([$sellable_type->sellable_type, 'deleting'], [function($node){
                    if(CartSellable::whereSellableType(get_class($node))->whereSellableId($node->id)->exists()){
                        throw new \Exception(__("Ce produit est enregistré dans un panier, il ne peut pas être supprimé."));
                        return false;
                    }
                }]);
            }
            $sellable_types = OrderItem::selectRaw('DISTINCT sellable_type')->get();
            foreach($sellable_types as $sellable_type) {
                call_user_func_array([$sellable_type->sellable_type, 'deleting'], [function($node){
                    if(OrderItem::whereSellableType(get_class($node))->whereSellableId($node->id)->exists()) {
                        throw new \Exception(__("Ce produit est enregistré dans une commande, il ne peut pas être supprimé."));
                        return false;
                    }
                }]);
            }
    	});
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    	$this->app->singleton("ryshop", function($app){
    		return new Shop();
    	});
    	
    	$this->app->singleton("ryshop.command", function($app){
    		return new ShopSetup();
		});
		
		$this->app->singleton("ryshop.reminder", function($app){
    		return new ExpiredReminder();
		});
    	
    	$this->commands(["ryshop.command", "ryshop.reminder"]);
    }
    public function map()
    {    	
    	if (! $this->app->routesAreCached()) {
    		$this->app['router']->group(['namespace' => 'Ry\Shop\Http\Controllers'], function(){
    			require __DIR__.'/../Http/routes.php';
    		});
    	}
    }
}