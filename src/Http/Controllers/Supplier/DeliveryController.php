<?php 
namespace Ry\Shop\Http\Controllers\Supplier;

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
use Ry\Pim\Models\Supplier\Supplier;

class DeliveryController extends Controller
{
    use ActionControllerTrait;
    
    public function __construct() {
        $this->middleware('supplierauth:supplier');
    }
    
    public function get_rates(Request $request) {
        $zones = Zone::with(["centrale", "rates" => function($q){
            $q->whereHas("carrier");
        }, "rates.prices.currency", "rates.carrier"])->get();
        $zones->map(function($zone){
            if($zone->centrale)
                $zone->centrale->append('nsetup');
            $zone->rates->map(function($rate){
                $rate->append('nsetup');
            });
        });
        return view("ldjson", [
            "view" => "Supplier.Account.Delivery",
            "data" => $zones,
            "select_carriers" => Carrier::all(),
            "currency" => app("centrale")->getCurrency(),
            "page" => [
                "title" => __("Frais de livraison"),
                "href" => __("/shop/delivery/rates")
            ]
        ]);
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
    
    public function post_rates(Request $request) {
        $ar = $request->all();
        $ar = Supplier::unescape($ar);
        $me = app("pim.supplier")->getLogged();
        $supplier = Supplier::find($me->supplying->supplier_id);
        $supplier_setup = $supplier->nsetup;
        $supplier_setup = array_replace_recursive($supplier_setup, $ar['nsetup']);
        /*$supplier_setup['carriage_paid'] = $ar['nsetup']['carriage_paid'];
        $supplier_setup['delivery']['delay'] = $ar['nsetup']['delivery']['delay'];
        $supplier_setup['order']['minimum'] = $ar['nsetup']['order']['minimum'];
        $supplier_setup['preferred_carriers'] = $ar['nsetup']['preferred_carriers'];*/
        $supplier->nsetup = $supplier_setup;
        $supplier->save();
        return redirect(__('/shop/delivery/rates'))->with('message', [
            'content' => __('Vos tarifs de livraisons ont été mis-à-jour avec succès'),
            'class' => 'alert alert-success'
        ]);
    }
}
?>