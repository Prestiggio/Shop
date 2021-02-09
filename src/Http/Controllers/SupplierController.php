<?php 
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Ry\Shop\Models\Order;
use Ry\Shop\Models\OrderInvoice;
use Illuminate\Http\Request;
use Ry\Pim\Models\Supplier\Supplier;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Ry\Shop\Models\Delivery\Carrier;

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
    
    public function get_orders(Request $request) {
        $me = app("pim.supplier")->getLogged();
        $query = Order::with(['items', 'buyer'])->whereSellerType(Supplier::class)
        ->whereSellerId($me->supplying->supplier_id)
        ->where('ry_shop_orders.setup->type', 'marketplace')->orderBy('ry_shop_orders.id', 'desc');
        if($request->has('cart_id')) {
            $query->whereCartId($request->get('cart_id'));
        }
        $orders = $query->paginate($this->perpage);
        $orders->map(function($order){
            $order->append('nsetup');
            $order->items->map(function($order_item){
                $order_item->append('nsetup');
            });
        });
        return view("ldjson", [
            "view" => "Supplier.Marketplace.Order.List",
            "data" => $orders,
            "type" => "order",
            "currency" => app("centrale")->getCurrency(),
            "page" => [
                "href" => __('/marketplace/orders'),
                "title" => __("Bons de commande marketplace")
            ]
        ]);
    }
    
    public function get_order(Request $request) {
        $me = app("pim.supplier")->getLogged();
        $order = Order::with(['items.sellable.product.medias', 'buyer.mainUser.profile', 'buyer.mainUser.contacts', 'cart.deliveryAddress.ville.country', 'cart.billingAddress.ville.country'])->whereSellerType(Supplier::class)
        ->whereSellerId($me->supplying->supplier_id)
        ->where('ry_shop_orders.setup->type', 'marketplace')->find($request->get('id'));
        $order->append('nsetup');
        $order->items->map(function($order_item){
            $order_item->append('nsetup');
            $order_item->sellable->append(['nsetup', 'visible_specs']);
            $order_item->sellable->product->append(['details']);
        });
        $order->setAttribute('pdf_link', __("/marketplace/order?id=:id&format=pdf", ['id' => $order->id]));
        if($request->has('format') && $request->get('format')=='pdf') {
            return $order->sellerPdf(Destination::INLINE);
        }
        $code = '';
        if(isset($order->nsetup['carrier']['id'])) {
            $order->setAttribute('carrier', Carrier::with('medias')->find($order->nsetup['carrier']['id']));
        }
        if(isset($order->nsetup['supplier_serial'])) {
            $code = $order->nsetup['supplier_serial'];
        }
        elseif($order->nsetup['serial']) {
            $code = $order->nsetup['serial'];
        }
        $order->setAttribute('vat', app("centrale")->getVat());
        return view("ldjson", [
            "view" => "Supplier.Marketplace.Order.Detail",
            "data" => $order,
            "type" => "order",
            "currency" => app("centrale")->getCurrency(),
            "parents" => [
                [
                    "title" => __("Mes bons de commande"),
                    "href" => __("/marketplace/orders")
                ]
            ],
            "page" => [
                "href" => __('/marketplace/orders'),
                "title" => __("Bons de commande") . ' ' . $code
            ]
        ]);
    }
    
    public function get_invoices(Request $request) {
        $me = app("pim.supplier")->getLogged();
        $query = OrderInvoice::with('order.items')->whereSellerType(Supplier::class)
        ->whereSellerId($me->supplying->supplier_id);
        $invoices = $query->paginate($this->perpage);
        $invoices->map(function($invoice){
            $invoice->append('nsetup');
            $invoice->order->items->map(function($order_item){
                $order_item->append('nsetup');
            });
        });
        return view("ldjson", [
            "view" => "Supplier.Marketplace.Order.Invoice.List",
            "data" => $invoices,
            "type" => "invoice",
            "page" => [
                "href" => __('/marketplace/invoices?cart_id=:cart_id', ['cart_id' => $request->get('cart_id')]),
                "title" => __("Facture marketplace")
            ]
        ]);
    }
}
?>