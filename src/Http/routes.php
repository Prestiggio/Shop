<?php
Route::group(["middleware" => ["web", "inshop"]], function(){
	Route::group(["middleware" => "admin"], function(){
		Route::any("ry/shop/admin/{action}", "AdminController@controller_action")->where("action", ".*");
	});
	Route::group(["middleware" => "auth"], function(){
		Route::get("ry/shop/invoices/download/{invoice}", "UserController@download")->where("invoice", "\w+");
		Route::get("ry/shop/invoices/{invoice}", "UserController@invoiceDetail")->where("invoice", "\w+");
		Route::any("ry/shop/membre/{action}", "UserController@controller_action")->where("action", ".*");
	});
	Route::any("ry/shop/{action}", "PublicController@controller_action")->where("action", ".*");
});
