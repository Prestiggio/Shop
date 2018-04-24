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

class RyServiceProvider extends ServiceProvider
{
	/**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	parent::boot();
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
    	
    	$this->commands(["ryshop.command"]);
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