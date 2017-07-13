<?php

namespace Ry\Shop\Console\Commands;

use Illuminate\Console\Command;

use Ry\Shop\Models\Currency;
use Illuminate\Database\Eloquent\Model;

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
        Model::reguard();
    }
}
