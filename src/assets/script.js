(function(window, angular, $, appApp, undefined){
	Date.prototype.toYMD = Date_toYMD;
    function Date_toYMD() {
        var year, month, day, hours, minutes, seconds;
        year = String(this.getFullYear());
        month = String(this.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(this.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        hours = String(this.getHours());
        if (hours.length == 1) {
        	hours = "0" + hours;
        }
        minutes = String(this.getMinutes());
        if (minutes.length == 1) {
        	minutes = "0" + minutes;
        }
        seconds = String(this.getSeconds());
        if (seconds.length == 1) {
        	seconds = "0" + seconds;
        }
        return year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds;
    };
    String.prototype.toDate = String2Date;
    function String2Date() {
    	var s = this.match(/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/);
    	var d = new Date();
    	d.setFullYear(s[1]);
    	d.setMonth(parseInt(s[2])+1);
    	d.setDate(parseInt(s[3]));
    	d.setHours(parseInt(s[4]));
    	d.setMinutes(parseInt(s[5]));
    	d.setSeconds(parseInt(s[6]));
    	return d;
    };
    
    angular.module("ngRyShop", ["ngApp"]).service("$shopping", ["$sessionStorage", "$appSetup", "$q", function($sessionStorage, $app, $q){
    	
    	if(!$sessionStorage.cart) {
    		$sessionStorage.cart = {
    			items : {},
    			currency : $app.data.conf.shop.currency,
    			amount : 0
    		};
    	}
    	
    	this.save = function(){
    		var initDeferred = $q.defer();
    		if($sessionStorage.followed) {
    			this.add($sessionStorage.followed);
        		delete $sessionStorage.followed;
    		}
    		initDeferred.resolve();
        	return initDeferred.promise;
    	};
    	
    	this.follow = function(item) {
    		$sessionStorage.followed = item;
    	};
    	
    	this.unfollow = function() {
    		delete $sessionStorage.followed;
    	};
    	
    	this.add = function(item){
    		if(!item)
    			return;
    		$sessionStorage.cart.items[item.id] = item;
    		this.total();
    	};
    	
    	this.cart = function(){
    		return $sessionStorage.cart;
    	};
    	
    	this.update = function(items){
    		var dis = this;
    		$sessionStorage.cart.items = {};
    		angular.forEach(items, function(item){
    			dis.add(item);
    		});
    	};
    	
    	this.remove = function(item){
    		delete $sessionStorage.cart.items[item.id];
    		this.total();
		};
		
		this.hook_discount = function(total){
			return {
				value : 0
			};
		};
    	
    	this.total = function(){
    		var amount = 0;
    		for(var it in $sessionStorage.cart.items) {
    			var i = $sessionStorage.cart.items[it];
    			i.cart_amount = parseFloat(i.cart_unitprice ? i.cart_unitprice*i.cart_quantity : 0);
    			amount += i.cart_amount;
    		}
			$sessionStorage.cart.amount = amount;
			var discount = this.hook_discount(amount);
    		if($sessionStorage.cart.tva && $sessionStorage.cart.tva>0) {
    			$sessionStorage.cart.tax = parseFloat($sessionStorage.cart.tva) / 100 * (parseFloat($sessionStorage.cart.amount) - parseFloat(discount.value));
    		}
    		else {
    			$sessionStorage.cart.tax = 0;
    		}
			$sessionStorage.cart.amountTTC = $sessionStorage.cart.amount - parseFloat(discount.value) + $sessionStorage.cart.tax;
			$sessionStorage.cart.discount = discount;
    		return $sessionStorage.cart.amount;
    	};
    	
    }]).service("rypaypal", ["$appSetup", "$q", function($app, $q){
		
		var initDeferred = $q.defer();
		
		$.getScript("https://www.paypalobjects.com/api/checkout.js", function(){
			initDeferred.resolve();
		});
		
		return initDeferred.promise;
		
	}]).filter("sep1000", function(){
		return function(target){
			if(target)
				return target.toString().replace(/\./, ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
			return target;
		};
	}).filter("sep1000nodec", function(){
		return function(target){
			if(target)
				return target.toString().replace(/\./, ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".").split(",")[0];
			return target;
		};
	}).directive("rypaypal", ["rypaypal", "$appSetup", "$http", "$window", function(rypaypal, $app, $http, $window){
		
		return {
			require : "ngModel",
			restrict : "A",
			template : '<div id="paypal-button" class="dropin-container"></div>',
			link : function(scope, elem, attrs, ngModel){
				rypaypal.then(function(){
					paypal.Button.render({

			            env: 'production',
			            
			            client : {
			            	sandbox : "AftsxEyruMp62Ya3cEXm6MjkiB9-Fss_ehRmbKZ41kNBgyOnU1n6VjLojRrS1y9qzzAafNw7wQ4xbnDZ",
			            	production : "AVxZLWVvhRB4rYmbX-oiEKix3GMm3VTMywBr543xiCCbQmFjNnjHxcpEPaEH36Me4IN-Je_kyOk0DCFm"
			            },

			            commit: true, // Show a 'Pay Now' button

			            payment: function(data, actions) {
			            	return actions.payment.create({
			                    payment: {
			                        transactions: [
			                            {
			                                amount: { total: ngModel.$viewValue.amount/2000, currency: "USD" }
			                            }
			                        ]
			                    }
			                });
			            },

			            onAuthorize: function(data, actions) {
			            	return actions.payment.execute().then(function(payment) {
			            		ngModel.$viewValue.payment = payment;
							    $http.post(attrs.action, ngModel.$viewValue).then(function(response){
							    	$window.location.href = response.data.redirect;
							    });
			                });
			           }

			        }, '#paypal-button');
					
					
					/*braintree.dropin.create({
						authorization: $app.data.conf.ryshop.paypal.bttoken,
						container: $(elem).find('.dropin-container')[0]
					}, function (createErr, instance) {
						$(elem).find("button").on("click", function(){
							instance.requestPaymentMethod(function (err, payload) {
								ngModel.$viewValue.payment = payload;
							    $http.post(attrs.action, ngModel.$viewValue).then(function(response){
							    	$window.location.href = response.data.redirect;
							    });
							});
						});
					});*/
				});
			}
		};
		
	}]).directive("mgMobileShop", function(){
		
		return {
			restrict : "AC",
			link : function(scope, elem, attrs, ctrl){
				
			}
		};
		
	});
    
})(window, window.angular, window.jQuery, window.appApp);

window.ryshop = {version:{full: "1.0.0"}};
