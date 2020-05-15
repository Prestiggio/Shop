<?php 
namespace Ry\Shop\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Ry\Admin\Http\Traits\ActionControllerTrait;
use Illuminate\Http\Request;
use Ry\Shop\Models\Delivery\Carrier;
use Ry\Medias\Models\Media;
use Illuminate\Support\Facades\Auth;
use Ry\Shop\Models\Delivery\Zone;
use Ry\Shop\Models\Delivery\CarrierZoneRate;
use Ry\Shop\Models\Price\Price;
use Ry\Shop\Models\Shop;
use Ry\Centrale\Models\Site;

class DeliveryController extends Controller
{
    use ActionControllerTrait;
    
    public function __construct() {
        $this->middleware("managerauth:manager");
    }
    
    public function get_carriers(Request $request) {
        return view("ldjson", [
            "view" => "Ry.Shop.Delivery.Carrier.List",
            "data" => Carrier::with(['medias'])->get(),
            "page" => [
                "title" => __("Liste des transporteurs"),
                "href" => __("/shop/delivery/carriers")
            ]
        ]);
    }
    
    public function post_carrier(Request $request) {
        $ar = $request->all();
        if(isset($ar['carriers'])) {
            foreach ($ar['carriers'] as $id => $carrier) {
                if($id>0) {
                    $_carrier = Carrier::find($id);
                    $carrier_setup = array_replace_recursive($_carrier->nsetup, $carrier['nsetup']);
                }
                else {
                    $_carrier = new Carrier();
                    $carrier_setup = $carrier['nsetup'];
                }
                $_carrier->name = $carrier['name'];
                $_carrier->nsetup = $carrier_setup;
                $_carrier->save();
                if(isset($carrier['medias'][0]['id'])) {
                    $media = Media::find($carrier['medias'][0]['id']);
                    $media->mediable_type = Carrier::class;
                    $media->mediable_id = $_carrier->id;
                    $media->save();
                }
                app("centrale")->toSite($_carrier);
            }
        }
        return [
            "type" => "carriers",
            "data" => Carrier::with(['medias'])->get()
        ];
    }
    
    public function delete_carrier(Request $request) {
        $carrier = Carrier::find($request->get('id'));
        if($carrier) {
            app("centrale")->delete($carrier);
            $carrier->delete();
        }
        return [
            'type' => 'carriers',
            'data' => Carrier::with(['medias'])->get()
        ];
    }
    
    public function post_carrier_upload(Request $request) {
        $me = Auth::user();
        $media = Media::find($request->get('id'));
        if(!$media) {
            $media = new Media();
        }
        $media->path = 'storage/' . $request->file('file')->storePublicly('shop/delivery/carriers');
        $media->owner_id = $me->id;
        $media->save();
        return $media;
    }
    
    public function get_rates(Request $request) {
        $zones = Zone::with(["centrale", "rates.carrier", "rates.prices.currency"])->get();
        $zones->map(function($zone){
            if($zone->centrale)
                $zone->centrale->append('nsetup');
            $zone->rates->map(function($rate){
                $rate->append('nsetup');
            });
        });
        return view("ldjson", [
            "view" => "Ry.Shop.Delivery.Rate.List",
            "data" => $zones,
            "select_carriers" => Carrier::all(),
            "currency" => app("centrale")->getCurrency(),
            "page" => [
                "title" => __("Frais de livraison"),
                "href" => __("/shop/delivery/rates")
            ]
        ]);
    }
    
    public function post_zone(Request $request) {
        $ar = $request->all();
        if(!isset($ar['centrale']['nsetup']['active'])) {
            abort(500, "Veuillez d'abord activer cette zone");
        }
        $site = app("centrale")->getSite();
        $shop = Shop::whereOwnerType(Site::class)->whereOwnerId($site->id)->first();
        if(!$shop) {
            $shop = new Shop();
            $shop->name = $site->hostname;
            $shop->owner_type = Site::class;
            $shop->owner_id = $site->id;
            $shop->save();
        }
        $zone = Zone::with(['rates'])->find($request->get('id'));
        app("centrale")->toSite($zone, null, [
            'active' => true
        ]);
        if(isset($ar['rates'])) {
            foreach($ar['rates'] as $rate_id => $rate) {
                if($rate_id>0) {
                    $_rate = $zone->rates()->find($rate_id);
                    if(!$rate)
                        continue;
                    $rate_setup = array_replace_recursive($_rate->nsetup, $rate['nsetup']);
                }
                else {
                    $_rate = new CarrierZoneRate();
                    $_rate->carrier_id = $rate['carrier_id'];
                    $_rate->zone_id = $zone->id;
                    $rate_setup = $rate['nsetup'];
                }
                $_rate->nsetup = $rate_setup;
                $_rate->save();
                $price = $_rate->prices()->first();
                if(!$price) {
                    $price = new Price();
                    $price->shop_id = $shop->id;
                    $price->priceable_type = CarrierZoneRate::class;
                    $price->priceable_id = $_rate->id;
                }
                $price->currency_id = $rate['prices'][0]['currency_id'];
                $price->price = $rate['prices'][0]['price'];
                $price->save();
            }
        }
        $zone->load(["rates.carrier", "rates.prices.currency", "centrale"]);
        if($zone->centrale)
            $zone->centrale->append('nsetup');
        $zone->rates->map(function($rate){
            $rate->append('nsetup');
        });
        $zone->setAttribute('type', 'zone');
        return $zone;
    }
    
    public function delete_rate(Request $request) {
        $rate = CarrierZoneRate::find($request->get('id'));
        $zone_id = $rate->zone_id;
        $rate->delete();
        $zone = Zone::with(["rates.carrier", "rates.prices.currency", "centrale"])->find($zone_id);
        if($zone->rates()->count()==0) {
            app("centrale")->toSite($zone, null, [
                "active" => false
            ]);
        }
        else {
            if($zone->centrale)
                $zone->centrale->append('nsetup');
            $zone->rates->map(function($rate){
                $rate->append('nsetup');
            });
        }
        $zone->setAttribute('type', 'zone');
        return $zone;
    }
}
?>