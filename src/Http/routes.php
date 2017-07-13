<?php
Route::group(["middleware" => ["web", "inshop"]], function(){
	Route::group(["middleware" => "admin"], function(){
		Route::controller("ry/shop/admin", "AdminController");
	});
	Route::group(["middleware" => "auth"], function(){
		Route::get("ry/shop/invoices/download/{invoice}", "UserController@download")->where("invoice", "\w+");
		Route::get("ry/shop/invoices/{invoice}", "UserController@invoiceDetail")->where("invoice", "\w+");
		Route::controller("ry/shop/membre", "UserController");
	});
	Route::controller("ry/shop", "PublicController");
});
