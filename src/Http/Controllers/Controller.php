<?php 
namespace Ry\Shop\Http\Controllers;

use App\User;
use Faker\Factory;
use Illuminate\Http\Request;
use Ry\Admin\Http\Controllers\AdminController;
use Ry\Admin\Models\Permission;
use Ry\Admin\Models\Role;
use Ry\Admin\Models\UserRole;
use Ry\Admin\Models\Layout\LayoutSection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Ry\Profile\Models\Contact;
use Ry\Profile\Models\NotificationTemplate;
use Ry\Categories\Models\Categorie;
use Ry\Categories\Http\Controllers\AdminController as CategorieAdminController;
use Ry\Pim\Models\Sourcing\Sourcing;
use Ry\Pim\Models\Supplier\Supplier;
use Ry\Pim\Models\Supplier\SupplyingUser;
use Ry\Shop\Models\Currency;
use Ry\Geo\Models\Country;
use Auth, App;
use Ry\Medias\Models\Media;
use Ry\Geo\Http\Controllers\PublicController;
use Ry\Shop\Models\Bank\Bank;
use Ry\Pim\Models\Product\Product;
use Ry\Pim\Models\Product\Variant;
use Ry\Pim\Models\Sourcing\SourcingUser;
use Ry\Pim\Models\Product\Option;
use Ry\Campagnes\Models\Campagne;
use Ry\Affiliate\Models\Affiliate;
use Ry\Pim\Models\Warehouse\Warehouse;
use Ry\Opportunites\Models\Contract;
use Ry\Opportunites\Models\QuotesRequestGroup;
use Ry\Pim\Models\Product\VariantSourcing;
use Illuminate\Support\Collection;
use Ry\Categories\Models\Categorygroup;
use Ry\Shop\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ry\Centrale\SiteScope;
use Ry\Admin\Models\Pretention;
use Illuminate\Support\Facades\Storage;
use Ry\Campagnes\Models\CampagneAffiliate;
use Ry\Affiliate\Models\AffiliateUser;
use Ry\Pim\Models\Warehouse\WarehouseUser;
use Ry\Opportunites\Models\Product\Product as OpProduct;
use Ry\Campagnes\Models\Product\Product as CpProduct;
use Ry\Affiliate\Models\AffiliateGroup;
use Ry\Pim\Http\Controllers\Product\OptionController;
use App\Http\Controllers\Controller as BaseController;
use Ry\Opnegocies\Models\Opnegocie;

class Controller extends BaseController
{   
    public function detail(Request $request) {
        $permission = Permission::authorize(__METHOD__);
        $order = Order::with(['buyer.adresse.ville.country', 'buyer.deliveryAdresse.ville.country', 'buyer.contacts', 'seller', 'items.sellable.product.medias', 'cart.deliveryAddress.ville.country', 'cart.billingAddress.ville.country'])->find($request->get('id'));
        if(isset($order->nsetup['operation_id']) && $order->nsetup['type']=='opportunites')
            $order->setAttribute('operation', QuotesRequestGroup::find($order->nsetup['operation_id']));
        elseif(isset($order->nsetup['operation_id']) && $order->nsetup['type']=='opnegocies')
            $order->setAttribute('operation', Opnegocie::find($order->nsetup['operation_id']));
        $order->append('nsetup');
        if($order->cart)
            $order->cart->append('nsetup');
        $order->items->each(function($item){
            $item->append('nsetup');
            $item->sellable->append('nsetup');
        });
        return view("ldjson", [
            "view" => "Ry.Shop.Orders.Detail",
            "data" => $order,
            "parents" => [
                [
                    "title" => $order->buyer_type==Affiliate::class ? __("Commandes affiliés") : __("Commandes fournisseurs"),
                    "href" => $order->buyer_type==Affiliate::class ? __("/affiliate_orders") : __("/supplier_orders")
                ]
            ],
            "page" => [
                "title" => __("Détail commande"),
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
                "title" => __("Commandes"),
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