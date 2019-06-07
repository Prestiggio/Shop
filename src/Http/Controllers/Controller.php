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

class Controller extends BaseController
{
    protected $theme;
    
    public function setTheme($theme) {
        $this->theme = $theme;
        return $this;
    }
    
    public function list() {
        $permission = Permission::authorize(__METHOD__);
        return view("$this->theme::ldjson", [
            "theme" => $this->theme,
            "view" => "Shop.Currencies",
            "data" => Currency::paginate(10),
            "page" => [
                "title" => __("codes_devises"),
                "href" => __("get_currencies"),
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