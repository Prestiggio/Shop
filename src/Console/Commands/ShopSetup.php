<?php

namespace Ry\Shop\Console\Commands;

use Illuminate\Console\Command;

use Ry\Admin\Models\Role;
use Ry\Shop\Models\Currency;
use Ry\Shop\Models\Shop;
use Illuminate\Database\Eloquent\Model;
use App\User;

class ShopSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Model::unguard();
        Currency::create([
        		"name" => "Ariary",
        		"iso_code" => "MGA",
        		"conversion_rate" => 1
        ]);
        $admin = Role::where("name", "=", "admin");
        if($admin->exists()) {
        	$user = $admin->first()->user;
        }
        else {
        	$user = User::first();
        }
        $adresse = app("\Ry\Geo\Http\Controllers\PublicController")->generate([
        		"raw" => env("COMPANY_ADRESSE", "LOT IBK 44 Bis Ampasamadinika"),
        		"ville" => [
        				"nom" => env("COMPANY_VILLE", "Antananarivo"),
        				"cp" => env("COMPANY_CP", 101),
        				"country" => [
        						"nom" => env("COMPANY_COUNTRY", "Madagascar")
        				]
        		]
        ]);
        Shop::where("id", "=", 1)->first()->owner->companies()->create([
        		"editor_id" => $user->id,
        		"editor_post" => "owner",
        		"nom" => env("COMPANY", "TOPMORA SHOP"),
        		"adresse_id" => $adresse->id
        ]);
        Model::reguard();
    }
}
