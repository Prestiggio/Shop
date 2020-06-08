<?php 
namespace Ry\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Ry\Affiliate\Models\Affiliate;
use Ry\Categories\Models\Categorie;
use Ry\Pim\Models\Product\Product;
use Ry\Pim\Models\Supplier\Supplier;
use Ry\Shop\Models\CartSellable;
use Ry\Shop\Models\Order;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\ShopGroup;
use Ry\Pim\Models\Product\Option;
use Ry\Pim\Models\Product\VariantSupplier;
use Ry\Pim\Models\Product\Variant;
use Ry\Shop\Models\OrderItem;
use Ry\Shop\Models\Price\Price;
use Ry\Shop\Models\Delivery\CarrierZoneRate;
use Ry\Shop\Models\Cart;
use Ry\Geo\Models\Country;
use Ry\Geo\Http\Controllers\PublicController as GeoController;
use Ry\Shop\Models\OrderInvoice;

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
    
    public function get_products(Request $request) {
        $ar = $request->all();
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
        $query = Product::with(["medias", "variants" => function($q){
            $q->join("ry_shop_prices", "ry_shop_prices.priceable_id", "=", "ry_pim_product_variants.id")
            ->wherePriceableType(Variant::class)->select("ry_pim_product_variants.*")->groupBy('ry_pim_product_variants.id');
        }, "variants.sourcings", "categories", "variants.product"])
        ->whereHas("variants")->where('ry_centrale_site_restrictions.setup->domain', 'marketplace')->select("ry_pim_products.*");
        if($request->has('supplier_id')) {
            $supplier_id = $request->get('supplier_id');
            $query->with(['variants' => function($q)use($supplier_id){
                $q->whereHas("suppliers", function($q)use($supplier_id){
                    $q->where("ry_pim_suppliers.id", "=", $supplier_id);
                });
            }])->whereHas('variants.suppliers', function($q)use($supplier_id){
                $q->where("ry_pim_suppliers.id", "=", $supplier_id);
            });
        }
        $products = $query->paginate($this->perpage);
        $shop_commissions = [];
        $products->map(function($product)use(&$nvariants, $commissions, $arcommissions, $me, $shop_group, &$shop_commissions){
            $product->append('details');
            $product->append('href');
            $product->variants->map(function($item)use(&$nvariants, $commissions, $arcommissions, $me, $shop_group, &$shop_commissions){
                $prices = Price::with('shop.owner')->wherePriceableType(Variant::class)->wherePriceableId($item->id)
                ->whereHas('shop', function($q)use($shop_group){
                    $q->whereShopGroupId($shop_group->id);
                })->orderBy('price')->take(1)->get();
                foreach($prices as $price) {
                    $price->shop->owner->append('nsetup');
                    if(!isset($shop_commissions[$price->shop_id])) {
                        $shop_commissions[$price->shop_id] = isset($price->shop->owner->centrale->nsetup['commissions']) ? $price->shop->owner->centrale->nsetup['commissions'] : [];
                    }
                    $supplier_commissions = 0;
                    foreach($shop_commissions[$price->shop_id] as $icommission) {
                        $supplier_commissions+=floatval($icommission);
                    }
                    $price->setAttribute('commissions', [
                        'affiliate' => $arcommissions,
                        'supplier' => $shop_commissions
                    ]);
                    $price->setAttribute('unit_price_commissionned', $price->price*(1+($commissions+$supplier_commissions)/100));
                }
                $item->setAttribute('sellable_nsetup', [
                    'prices' => $prices
                ]);
                $item->append('nsetup');
                $item->append("visible_specs");
                $nvariants++;
            });
        });
        $filtered = isset($ar['s']['options']);
        $categories = Categorie::cacheGroup('product', $site->id)->filter(function($item){
            return $item->active;
        });
        $options = Option::where('ry_pim_product_options.setup->in_filter', true)->get();
        $options->map(function(&$option)use($ar){
            $form = $option->form;
            if(isset($form['options']) && $form['options'] instanceof Collection) {
                Categorie::attributeAll($form['options'], ['show' => false]);
                $ids = Variant::selectRaw("DISTINCT(JSON_UNQUOTE(
                        JSON_EXTRACT(
                            JSON_EXTRACT(setup, JSON_UNQUOTE(
                                                    REPLACE(JSON_SEARCH(setup, 'one', '{$option->name}'), '.option', ''))),
                         '$.id'))) AS d")->pluck("d")->toArray();
                Categorie::attributeByIds($form['options'], $ids, ['show' => true]);
                if(isset($ar['s']['options'][$option->name]) && count($ar['s']['options'][$option->name])) {
                    Categorie::attributeByIds($form['options'], $ar['s']['options'][$option->name], ['selected' => true]);
                }
                $option->form = $form;
            }
            $option->append('form');
        });
        return view("ldjson", [
            "type" => "products",
            "view" => "Affiliate.Marketplace.Products",
            "page" => [
                "title" => __("Liste des produits"),
                "href" => __("/marketplace/products")
            ],
            "data" => array_merge([
                'nvariants' => $nvariants,
                'specs_options' => Variant::getOptions(),
                "filtered" => $filtered,
                "categories" => $categories,
                "filtrable_options" => $options,
                "filtrable_categories" => [],
                "query" => isset($ar['s'])?[
                    "s" => $ar['s']
                ]:[],
            ], $products->toArray())
        ]);
    }
    
    public function get_product($slug, $id) {
        $me = app("affiliation")->getLogged();
        $arcommissions = isset($me->affiliation->details->nsetup['commissions']) ? $me->affiliation->details->nsetup['commissions'] : [];
        $commissions = 0;
        foreach($arcommissions as $icommission) {
            $commissions+=floatval($icommission);
        }
        $product = Product::with(["variants.sourcings", "medias", "categories"])->find($id);
        $product->append('details');
        $product->append('href');
        $product->variants->map(function($item)use($commissions, $arcommissions, $me){
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
            $prices = Price::with('shop.owner')->wherePriceableType(Variant::class)->wherePriceableId($item->id)
            ->whereHas('shop', function($q)use($shop_group){
                $q->whereShopGroupId($shop_group->id);
            })->get();
            foreach($prices as $price) {
                $price->shop->owner->append('nsetup');
                $price->setAttribute('unit_price_commissionned', $price->price*(1+$commissions/100));
            }
            $item->setAttribute('sellable_nsetup', [
                'prices' => $prices
            ]);
            $item->append('nsetup');
            $item->append("visible_specs");
        });
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Product.Detail",
            'data' => $product,
            "page" => [
                "title" => __("Opération :product", ["product" => $product->name]),
                "href" => __("/marketplace" . $product->href)
            ]
        ]);
    }
    
    public function get_billing_address(Request $request) {
        $carts = app("affiliation")->cart();
        if(isset($carts['mp'])) {
            $carts['mp']->append(['nsetup']);
            $carts['mp']->load(['billingAddress.ville.country']);
        }
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Order.BillingAddress",
            "vat" => 20,
            "countries" => Country::all(),
            "page" => [
                "title" => __("Adresse de facturation"),
                "href" => __("/marketplace/billing_address?cart_id=:cart_id", ['cart_id' => $request->get('cart_id')])
            ]
        ]);
    }
    
    public function get_delivery_address(Request $request) {
        $carts = app("affiliation")->cart();
        if(isset($carts['mp'])) {
            $carts['mp']->append(['nsetup']);
            $carts['mp']->load(['deliveryAddress.ville.country']);
        }
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Order.DeliveryAddress",
            "vat" => 20,
            "countries" => Country::all(),
            "page" => [
                "title" => __("Adresse de livraison"),
                "href" => __("/marketplace/delivery_address?cart_id=:cart_id", ['cart_id' => $request->get('cart_id')])
            ]
        ]);
    }
    
    public function post_cart(Request $request) {
        $ar = $request->all();
        $cart = Cart::find($ar['id']);
        $cart_setup = $cart->nsetup;
        if(isset($ar['shop'])) {
            foreach($ar['shop'] as $shop_id => $_shop) {
                $cart_setup['shop'][$shop_id] = $_shop['order']['nsetup'];
                foreach($_shop['variants'] as $product_variant_id => $variant) {
                    $sellable = $cart->items()->whereShopId($shop_id)->whereSellableId($product_variant_id)->whereSellableType(Variant::class)->find($variant['cart_sellable_id']);
                    $sellable->quantity = $variant['quantity'];
                    $sellable_setup = $sellable->nsetup;
                    $sellable_setup['total_price'] = $variant['total_price'];
                    $sellable->nsetup = $sellable_setup;
                    $sellable->save();
                }
            }
        }
        if(isset($ar['nsetup'])) {
            $cart_setup = array_replace_recursive($cart_setup, $ar['nsetup']);
        }
        if(isset($ar['billing_address'])) {
            $cart->billing_adresse_id = app(GeoController::class)->generate($ar['billing_address'])->id;
        }
        if(isset($ar['delivery_address'])) {
            $cart->delivery_adresse_id = app(GeoController::class)->generate($ar['delivery_address'])->id;
        }
        $cart->nsetup = $cart_setup;
        $cart->save();
        if($request->has('billing_address')) {
            return redirect(__('/marketplace/delivery_address?cart_id=:cart_id', ['cart_id' => $ar['id']]));
        }
        if($request->has('delivery_address')) {
            return redirect(__('/marketplace/payment?cart_id=:cart_id', ['cart_id' => $ar['id']]));
        }
        return redirect(__('/marketplace/billing_address?cart_id=:cart_id', ['cart_id' => $ar['id']]));
    }
    
    public function get_payment(Request $request) {
        $me = app("affiliation")->getLogged();
        $carts = app("affiliation")->cart();
        $carrier_rates = CarrierZoneRate::with('prices')
        ->whereHas("zone")
        ->whereHas("carrier")
        ->whereZoneId($me->affiliation->delivery_zone->id)->get();
        $carrier_rates->map(function($carrier_rate){
            $carrier_rate->append('nsetup');
        });
        if(isset($carts['mp'])) {
            $carts['mp']->append(['nsetup']);
        }
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Order.Payment",
            "vat" => 20,
            "carrier_rates" => $carrier_rates,
            "page" => [
                "href" => __("/marketplace/payment?cart_id=:cart_id", ['cart_id' => $request->get('cart_id')]),
                "title" => __("Paiement")
            ]
        ]);
    }
    
    public function post_order(Request $request) {
        $me = app("affiliation")->getLogged();
        $carts = app("affiliation")->cart();
        $cart = $carts['mp'];
        $ar = $cart->nsetup;
        $errors = [];
        if(isset($ar['shop'])) {
            OrderItem::unguard();
            foreach($ar['shop'] as $shop_id => $_shop) {
                $shop = Shop::find($shop_id);
                $supplier = $shop->owner;
                $order = Order::whereBuyerType(Affiliate::class)->whereBuyerId($me->affiliation->id)
                ->whereSellerType(Supplier::class)->whereSellerId($supplier->id)->whereCartId($cart->id)
                ->whereShopId($shop->id)->first();
                if(!$order) {
                    $order = new Order();
                    $order->buyer_type = Affiliate::class;
                    $order->buyer_id = $me->affiliation->id;
                    $order->seller_type = Supplier::class;
                    $order->seller_id = $supplier->id;
                    $order->cart_id = $cart->id;
                    $order->shop_id = $shop->id;
                    $order->nsetup = $_shop;
                    $order->save();
                    
                    app("centrale")->toSite($order);
                }
                else {
                    $order->nsetup = array_replace_recursive($order->nsetup, $_shop);
                    $order->save();
                }
                
                $invoice = $order->invoices()
                ->whereBuyerType(Affiliate::class)
                ->whereBuyerId($me->affiliation->id)
                ->whereSellerType(Supplier::class)
                ->whereSellerId($supplier->id)->first();
                if(!$invoice) {
                    $invoice = new OrderInvoice();
                    $invoice->order_id = $order->id;
                    $invoice->buyer_type = Affiliate::class;
                    $invoice->buyer_id = $me->affiliation->id;
                    $invoice->seller_type = Supplier::class;
                    $invoice->seller_id = $supplier->id;
                }
                $invoice->quantity = 1;
                $invoice->total_price = $order->nsetup['total_ttc'];
                $invoice->setup = $order->setup;
                $invoice->save();
                
                $cart_sellables = $cart->items()->whereShopId($shop_id)->get();
                
                foreach($cart_sellables as $cart_sellable) {
                    if($cart_sellable->quantity<=0)
                        continue;
                    
                    $order_item = $order->items()->whereSellableType(Variant::class)->whereSellableId($cart_sellable->sellable_id)->first();
                    if(!$order_item) {
                        $order_item = $order->items()->create([
                            'sellable_type' => Variant::class,
                            'sellable_id' => $cart_sellable->sellable_id,
                            'quantity' => $cart_sellable->quantity,
                            'price' => $cart_sellable->nsetup['total_price'],
                            'setup' => $cart_sellable->setup
                        ]);
                    }
                    else {
                        $order_item->quantity = $cart_sellable->quantity;
                        $order_item->price = $cart_sellable->nsetup['total_price'];
                        $order_item->setup = $cart_sellable->setup;
                        $order_item->save();
                    }
                }
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
        return redirect(__('/marketplace/invoices?cart_id=:cart_id', ['cart_id' => $cart->id]))->with('message', [
            'content' => __("Votre commande a été enregistré avec succès. Nous allons vous recontacter pour le mode de règlement de votre facture."),
            'class' => 'alert-success'
        ]);
    }
    
    public function get_invoices(Request $request) {
        $me = app("affiliation")->getLogged();
        $query = OrderInvoice::whereBuyerType(Affiliate::class)
        ->whereBuyerId($me->affiliation->id)
        ->whereSellerType(Supplier::class)
        ->with('order.items');
        if($request->has('cart_id')) {
            $cart_id = $request->get('cart_id');
            $query->whereHas('order.cart', function($q)use($cart_id){
                $q->whereCartId($cart_id);
            });
        }
        $invoices = $query->paginate($this->perpage);
        $invoices->map(function($invoice){
            $invoice->append('nsetup');
            $code = 'MP ' . $invoice->created_at->format('Y') . '-' . $invoice->id;
            $invoice->setAttribute('code', $code);
        });
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Order.Invoice.List",
            "data" => $invoices,
            "type" => "invoice",
            "page" => [
                "href" => __('/marketplace/invoices?cart_id=:cart_id', ['cart_id' => $request->get('cart_id')]),
                "title" => __("Paiement demandé")
            ]
        ]);
    }
    
    public function get_invoice(Request $request) {
        $me = app("affiliation")->getLogged();
        $invoice = OrderInvoice::whereBuyerType(Affiliate::class)
        ->whereBuyerId($me->affiliation->id)
        ->whereSellerType(Supplier::class)->with(['seller.adresse.ville.country', 'buyer.adresse.ville.country', 'buyer.users.profile', 'order.items.sellable.product.medias', 'order.cart.currency'])->find($request->get('id'));
        if(!$invoice) {
            abort(404);
        }
        $invoice->append('nsetup');
        $code = 'MP ' . $invoice->created_at->format('Y') . '-' . $invoice->id;
        $invoice->setAttribute('code', $code);
        $invoice->order->setAttribute('code', 'MP ' . $invoice->order->created_at->format('Y-m') . '-' . sprintf('%4d', $invoice->order->id));
        $invoice->order->items->map(function($order_item){
            $order_item->append('nsetup');
            $order_item->sellable->append('nsetup');
            $order_item->sellable->append('visible_specs');
        });
        $invoice->order->setAttribute('currency', $invoice->order->cart ? $invoice->order->cart->currency : app("centrale")->getCurrency());
        $invoice->setAttribute('pdf_link', __('/marketplace/invoice?id=:id&format=:format', ['id' => $request->get('id'), 'format' => 'pdf']));
        $invoice->setAttribute('xml_link', __('/marketplace/invoice?id=:id&format=:format', ['id' => $request->get('id'), 'format' => 'xml']));
        $invoice->setAttribute('csv_link', __('/marketplace/invoice?id=:id&format=:format', ['id' => $request->get('id'), 'format' => 'csv']));
        if($request->has('format')) {
            switch($request->get('format')) {
                default:
                    $pdf = new Html2Pdf();
                    $pdf->pdf->SetAuthor('Centrale');
                    $pdf->pdf->SetTitle('Facture ' . $invoice->code);
                    $pdf->pdf->SetSubject("Facture");
                    $pdf->setDefaultFont("Arial");
                    $pdf->writeHTML(view("ryshop::pdf", ["row" => $invoice])->render());
                    $pdf->Output(__("facture-:code.pdf", ['code' => date("Y-m-d-Hh-imn")])/*, "D"*/);
                    break;
            }
        }
        return view("ldjson", [
            "view" => "Affiliate.Marketplace.Order.Invoice.Detail",
            "vat" => 20,
            "data" => $invoice,
            "parents" => [
                [
                    "href" => __("/marketplace/invoices"),
                    "title" => __("Tous vos factures")
                ]
            ],
            "page" => [
                "href" => __("/marketplace/invoice?id=:id", ['id' => $request->get('id')]),
                "title" => __("Facture Nº:id", ["id" => $invoice->code])
            ]
        ]);
    }
    
    public function get_orders() {
        $me = app("affiliation")->getLogged();
        $query = Order::with(['items.sellable.product.medias'])
        ->join('ry_shop_order_items', 'ry_shop_order_items.order_id', '=', 'ry_shop_orders.id')
        ->whereBuyerType(Affiliate::class)->whereBuyerId($me->affiliation->id)->where('ry_shop_orders.setup->type', 'marketplace');
        $data = $query->orderBy('ry_shop_orders.id', 'desc')
        ->groupBy("ry_shop_orders.id")
        ->selectRaw("ry_shop_orders.*,
            SUM(ry_shop_order_items.price) AS total_price,
            COUNT(DISTINCT(ry_shop_order_items.sellable_id)) AS nvariants")
            ->paginate($this->perpage);
        $data->map(function($order){
            $order->setAttribute('code', 'MP-' . $order->created_at->format('ymd'). '-' . $order->id);
        });
        return view("ldjson", [
            "data" => $data,
            "view" => "Affiliate.Marketplace.Order.List",
            "page" => [
                "title" => __("Marketplace - Liste des bons de commande"),
                "href" => __("/marketplace/orders")
            ]
        ]);
    }
    
    public function get_order(Request $request) {
        $order = Order::with([
            "items.sellable.product.medias", 
            "buyer.users.profile",
            "buyer.adresse.ville.country",
            "buyer.warehouses.users.profile",
            "buyer.warehouses.adresse.ville.country"
        ])->find($request->get('id'));
        $code = 'MP-' . $order->created_at->format('ymd'). '-' . $order->id;
        $order->setAttribute('code', $code);
        $order->items->map(function($item){
            $item->append(['nsetup']);
            $item->sellable->append(['nsetup', 'visible_specs']);
            $item->sellable->product->append(['details', 'visible_specs']);
        });
        return view("ldjson", [
            "order" => $order,
            "affiliate" => $order->buyer,
            "view" => "Affiliate.Marketplace.Order.Detail",
            "page" => [
                "title" => __("Bon de commande :code", ["code" => $code]),
                "href" => __("/marketplace/order?id=:id", ['id' => $order->id])
            ]
        ]);
    }
    
    public function post_delivery_rates(Request $request) {
        $ar = $request->all();
        $price = 0;
        $rates = CarrierZoneRate::with('prices')->whereHas("zone")->whereCarrierId($ar['carrier_id'])->whereZoneId($ar['zone_id'])->get();
        foreach($rates as $rate) {
            $unit = 'Kg';
            if(isset($rate->nsetup['from']['unit']))
                $unit = $rate->nsetup['from']['unit'];
            switch($unit) {
                case 'Kg':
                    if(isset($ar['weight']) 
                    && $ar['weight']>=$rate->nsetup['from']['value'] 
                    && $ar['weight']<=$rate->nsetup['to']['value']
                    && $rate->prices()->count()>0)
                        $price = $rate->prices->first();
                    break;
            }
        }
        return $price;
    }
}
?>