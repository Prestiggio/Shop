<?php

namespace Ry\Shop\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

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
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
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