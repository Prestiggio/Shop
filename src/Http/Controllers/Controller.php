<?php 
namespace Ry\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Ry\Admin\Models\Permission;
use Ry\Shop\Models\Currency;
use Ry\Affiliate\Models\Affiliate;
use Ry\Centrale\Models\Site;
use Ry\Opportunites\Models\QuotesRequestGroup;
use Ry\Shop\Models\Order;
use App\Http\Controllers\Controller as BaseController;
use Ry\Opnegocies\Models\Opnegocie;
use Ry\Pim\Models\Product\VariantSupplier;
use Ry\Pim\Models\Supplier\Supplier;
use Ry\Shop\Models\Delivery\Carrier;

class Controller extends BaseController
{   
    public function detail(Request $request, $type) {
        $permission = Permission::authorize(__METHOD__);
        $order = Order::where('buyer_type', '!=', Site::class)->with(['buyer.adresse.ville.country', 'buyer.deliveryAdresse.ville.country', 'buyer.contacts', 'seller', 'items.sellable.product.medias', 'cart.deliveryAddress.ville.country', 'cart.billingAddress.ville.country'])->find($request->get('id'));
        if(!$order) {
            $order = Order::whereBuyerType(Site::class)->with(['buyer', 'seller', 'items.sellable.product.medias', 'cart.deliveryAddress.ville.country', 'cart.billingAddress.ville.country'])->find($request->get('id'));
            if($order) {
                $order->buyer->append(['name', 'adresse', 'deliveryAdresse', 'contacts']);
            }
        }
        if(!$order)
            abort(404);
        if(isset($order->nsetup['operation_id']) && $order->nsetup['type']=='opportunites')
            $order->setAttribute('operation', QuotesRequestGroup::find($order->nsetup['operation_id']));
        elseif(isset($order->nsetup['operation_id']) && $order->nsetup['type']=='opnegocies')
            $order->setAttribute('operation', Opnegocie::find($order->nsetup['operation_id']));
        $order->append('nsetup');
        if($order->cart)
            $order->cart->append('nsetup');
        if($order->seller_type==Supplier::class) {
            $supplier_id = $order->seller_id;
        }
        else {
            $supplier_id = false;
        }
        $order->items->each(function($item)use($supplier_id){
            $item->append('nsetup');
            $item->sellable->append('nsetup');
            if(!$supplier_id && isset($item->nsetup['supplier_id']))
                $supplier_id = $item->nsetup['supplier_id'];
            if($supplier_id) {
                $variant_supplier = VariantSupplier::whereSupplierId($supplier_id)->whereProductVariantId($item->sellable_id)->first();
                $item->setAttribute('supplier_setup', $variant_supplier->nsetup);
            }
        });
        if(isset($order->nsetup['carrier']['id'])) {
            $order->setAttribute('carrier', Carrier::with('medias')->find($order->nsetup['carrier']['id']));
        }
        $code = '';
        if($type=='buyer' && isset($order->nsetup['affiliate_serial'])) {
            $code = $order->nsetup['affiliate_serial'];
        }
        elseif($type=='seller' && isset($order->nsetup['supplier_serial'])) {
            $code = $order->nsetup['affiliate_serial'];
        }
        elseif(isset($order->nsetup['serial'])) {
            $code = $order->nsetup['serial'];
        }
        return view("ldjson", [
            "view" => "Ry.Shop.Orders.Detail",
            "mode" => $type,
            "data" => $order,
            "vat" => app("centrale")->getVat(),
            "parents" => [
                [
                    "title" => $type=="buyer" ? __("Commandes affiliés") : __("Commandes fournisseurs"),
                    "href" => $type=="buyer" ? __("/affiliate_orders") : __("/supplier_orders")
                ]
            ],
            "page" => [
                "title" => __("Détail commande :code", ["code" => $code]),
                "href" => __("/order?id=".$order->id),
                "icon" => "fa fa-cart",
                "permission" => $permission
            ]
        ]);
    }
    
    public function list(Request $request) {
        $permission = Permission::authorize(__METHOD__);
        $query = Order::with(['buyer', 'seller', 'items']);
        if($request->has('buyer_type')) {
            $query->where('buyer_type', '=', $request->get('buyer_type'));
        }
        if($request->has('seller_type')) {
            $query->where('seller_type', '=', $request->get('seller_type'));
        }
        $orders = $query->alpha()->paginate(10);
        return view("ldjson", [
            "view" => "Ry.Shop.Orders",
            "data" => $orders,
            "page" => [
                "title" => $request->get('type')=='buyer' ? __("Commandes affiliés") : __("Commandes fournisseurs"),
                "href" => __("/orders"),
                "icon" => "fa fa-cart",
                "permission" => $permission
            ]
        ]);
    }
    
    public function listCurrency() {
        $permission = Permission::authorize(__METHOD__);
        return view("ldjson", [
            "view" => "Ry.Shop.Currencies",
            "data" => Currency::paginate(10),
            "page" => [
                "title" => __("Codes devises"),
                "href" => __("/currencies"),
                "icon" => "fa fa-money-bill-wave",
                "permission" => $permission
            ]
        ]);
    }
    
    public function updateCurrency(Request $request) {
        Currency::find($request->get("id"))->update([
            $request->get("name") => $request->get("value")
        ]);
    }
    
    public function insertCurrency(Request $request) {
        $currency = new Currency();
        $currency->name = $request->get("name");
        $currency->iso_code = $request->get("iso_code");
        $currency->save();
        return $currency;
    }
}
?>