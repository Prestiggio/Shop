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

        Currency::create([
        		"name" => $this->ask("Nom de la devise ?"),
        		"iso_code" => $this->ask("Code iso (3 caractères majuscule) de la devise"),
                "symbol" => $this->ask("Symbole de la devise"),
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
        		"raw" => $this->ask("Adresse de la société"),
        		"ville" => [
        				"nom" => $this->ask("Nom de la ville"),
        				"cp" => $this->ask("Code postal"),
        				"country" => [
        						"nom" => $this->ask("Pays")
        				]
        		]
        ]);
        Shop::where("id", "=", 1)->first()->owner->companies()->create([
        		"editor_id" => $user->id,
        		"editor_post" => "owner",
        		"nom" => $this->ask("Nom de l'entreprise"),
        		"adresse_id" => $adresse->id
        ]);
    }
}
