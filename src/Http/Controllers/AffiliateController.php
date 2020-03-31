<?php 
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Ry\Affiliate\Models\Affiliate;
use Ry\Pim\Models\Product\Product;
use Ry\Pim\Models\Supplier\Supplier;
use Ry\Shop\Models\CartSellable;
use Ry\Shop\Models\Order;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\ShopGroup;
use Ry\Pim\Models\Product\VariantSupplier;
use Ry\Pim\Models\Product\Variant;
use Ry\Shop\Models\OrderItem;
use Ry\Shop\Models\Price\Price;

class AffiliateController extends Controller
{
    use ActionControllerTrait;
    
    private $perpage = 10;
    
    public function __construct() {
        $this->middleware('affiliateauth:affiliate');
        $this->me = Auth::user();
        if(app('centrale')) {
            $site = app('centrale')->getSite();
            if(isset($site->nsetup['affiliate_theme_option']['product']['perpage']))
                $this->perpage = $site->nsetup['affiliate_theme_option']['product']['perpage'];
            else
                $this->perpage = app('centrale')->perpage();
        }
    }
    
    public function get_products() {
        $me = app("affiliation")->getLogged();
        $site = app("centrale")->getSite();
        
        $shop_group = ShopGroup::where('setup->site_id', $site->id)->first();
        if(!$shop_group) {
            $shop_group = new ShopGroup();
            $shop_group->name = $site->hostname;
            $shop_group->nsetup = [
                "site_id" => $site->id,
                "share_customer" => true,
                "share_order" => true,
                "share_stock" => true
            ];
            $shop_group->active = $site->active;
            $shop_group->save();
        }
        $nvariants = 0;
        $arcommissions = isset($me->affiliation->details->nsetup['commissions']) ? $me->affiliation->details->nsetup['commissions'] : [];
        $commissions = 0;
        foreach($arcommissions as $icommission) {
            $commissions+=floatval($icommission);
        }
        $products = Product::with(["medias", "variants.sourcings", "categories", "variants.product"])
        ->whereHas("variants")->where('ry_centrale_site_restrictions.setup->domain', 'marketplace')->paginate($this->perpage);
        $products->map(function($product)use(&$nvariants, $commissions, $arcommissions, $me, $shop_group){
            $product->append('details');
            $product->append('href');
            $product->variants->map(function($item)use(&$nvariants, $commissions, $arcommissions, $me, $shop_group){
                $prices = Price::wherePriceableType(Variant::class)->wherePriceableId($item->id)
                ->whereHas('shop', function($q)use($shop_group){
                    $q->whereShopGroupId($shop_group->id);
                })->get();
                foreach($prices as $price) {
                    $price->setAttribute('unit_price_commissionned', $price->price*(1+$commissions/100));
                }
                $item->setAttribute('prices', $prices);
                $item->append('nsetup');
                $item->append("visible_specs");
                $nvariants++;
            });
        });
        return view("ryshop::ldjson", [
            "data" => $products,
            "view" => "Affiliate.Marketplace.Products",
            "page" => [
                "title" => __("Liste des produits"),
                "href" => __("/marketplace/products")
            ]
        ]);
    }
    
    public function post_order(Request $request) {
        $me = app("affiliation")->getLogged();
        $ar = $request->all();
        $errors = [];
        $site = app("centrale")->getSite();
        
        $shop_group = ShopGroup::where('setup->site_id', $site->id)->first();
        if(!$shop_group) {
            $shop_group = new ShopGroup();
            $shop_group->name = $site->hostname;
            $shop_group->nsetup = [
                "site_id" => $site->id,
                "share_customer" => true,
                "share_order" => true,
                "share_stock" => true
            ];
            $shop_group->active = $site->active;
            $shop_group->save();
        }
        
        if(isset($ar['variant_suppliers'])) {
            OrderItem::unguard();
            foreach($ar['variant_suppliers'] as $variant_supplier_id => $variant) {
                if(doubleval($variant['quantity'])<=0)
                    continue;
                
                $supplier = VariantSupplier::find($variant_supplier_id)->select(['supplier_id', 'product_variant_id'])->first();
                    
                $cart_sellable = CartSellable::find($variant['cart_sellable_id']);
                
                $shop = Shop::whereOwnerType(Supplier::class)->whereOwnerId($supplier->supplier_id)->whereShopGroupId($shop_group->id)->first();
                if(!$shop) {
                    $shop = new Shop();
                    $shop->owner_type = Supplier::class;
                    $shop->owner_id = $supplier->supplier_id;
                    $shop->shop_group_id = $shop_group->id;
                    $shop->active = true;
                    $shop->name = $this->owner->name;
                    $shop->save();
                }
                
                $order = Order::whereBuyerType(Affiliate::class)->whereBuyerId($me->affiliation->id)
                ->whereSellerType(Supplier::class)->whereSellerId($supplier->supplier_id)->whereCartId($ar['id'])
                ->whereShopId($shop->id)->first();
                if(!$order) {
                    $order = new Order();
                    $order->buyer_type = Affiliate::class;
                    $order->buyer_id = $me->affiliation->id;
                    $order->seller_type = Supplier::class;
                    $order->seller_id = $supplier->supplier_id;
                    $order->cart_id = $ar['id'];
                    $order->shop_id = $shop->id;
                    $order->shop_id = $shop->id;
                    $order->save();
                }
                
                $order_item = $order->items()->whereSellableType(Variant::class)->whereSellableId($supplier->product_variant_id)->first();
                if(!$order_item) {
                    $order_item = $order->items()->create([
                        'sellable_type' => Variant::class,
                        'sellable_id' => $supplier->product_variant_id,
                        'quantity' => $variant['quantity'],
                        'price' => $variant['total_price'],
                        'setup' => $cart_sellable->setup
                    ]);
                }
                else {
                    $order_item->quantity = $variant['quantity'];
                    $order_item->price = $variant['total_price'];
                    $order_item->setup = $cart_sellable->setup;
                    $order_item->save();
                }
                $cart_sellable->delete();
            }
            OrderItem::reguard();
        }
        if(count($errors)>0) {
            return redirect(__('/cart').'?source=mp')->with('message', [
                'content' => implode('<br/>', $errors),
                'class' => 'alert-warning'
            ]);
        }
        
        app("affiliation")->releaseCart('mp');
        return redirect(__('/marketplace/orders'))->with('message', [
            'content' => __("Votre commande a été enregistré avec succès."),
            'class' => 'alert-success'
        ]);
    }
    
    public function get_orders() {
        $me = app("affiliation")->getLogged();
        $query = Order::with(['items.sellable.product.medias'])->whereBuyerType(Affiliate::class)->whereBuyerId($me->affiliation->id);
        $data = $query->orderBy('ry_shop_orders.id', 'desc')
        ->groupBy("ry_shop_orders.buyer_id")
        ->selectRaw("ry_shop_orders.*,
            SUM(ry_shop_order_items.price) AS total_price,
            COUNT(DISTINCT(ry_shop_order_items.sellable_id)) AS nvariants")
            ->paginate($this->perpage);
        return view("ryshop::ldjson", [
            "data" => $data,
            "view" => "Affiliate.Markeplace.Order.List",
            "page" => [
                "title" => __("Marketplace - Liste des bons de commande"),
                "href" => __("/marketplace/orders")
            ]
        ]);
    }
}
?>