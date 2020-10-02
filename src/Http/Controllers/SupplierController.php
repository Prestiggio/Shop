<?php 
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ActionControllerTrait;
    
    private $perpage = 10;
    
    public function __construct() {
        $this->middleware('supplierauth:supplier');
        $site = app('centrale')->getSite();
        if(isset($site->nsetup['supplier_theme_option']['product']['perpage']))
            $this->perpage = $site->nsetup['supplier_theme_option']['product']['perpage'];
        else
            $this->perpage = app('centrale')->perpage();
    }
    
    public function get_products() {
        return view("ldjson", [
            "view" => "Supplier.Shop.Product.List",
            "page" => [
                "href" => __("/marketplace/products"),
                "title" => __("Mon catalogue marketplace")
            ]
        ]);
    }
    
    public function get_invoice(Request $request) {
        return $request->all();
    }
}
?>