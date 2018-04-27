<?php

namespace Ry\Shop\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Job;
use Ry\Shop\Models\Shop;
use Ry\Shop\Models\Customer;
use Ry\Shop\Models\Subscription;
use Mail;

class ExpiredReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryshop:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder to expiring subscriptions';

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
        $customers = Customer::whereHas("subscriptions", function($query){
            $query->whereRaw("(DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) = 60 OR DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) = 30 OR DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) = 15 OR DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) = 7 OR DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) = 3) AND DATEDIFF(DATE(ry_shop_subscriptions.expiry), CURDATE()) > 0");
        })->get();
        foreach($customers as $customer) {
            Mail::send("ryappeldoffres::emails.expiry", [
                "subscriptions" => $customer->subscriptions()->whereRaw("(DATEDIFF(DATE(expiry), CURDATE()) = 60 OR DATEDIFF(DATE(expiry), CURDATE()) = 30 OR DATEDIFF(DATE(expiry), CURDATE()) = 15 OR DATEDIFF(DATE(expiry), CURDATE()) = 7 OR DATEDIFF(DATE(expiry), CURDATE()) = 3) AND DATEDIFF(DATE(expiry), CURDATE()) > 0")->get(),
                "customer" => $customer,
                "shop" => Shop::find(1),
            ], function($message) use ($customer){
                $message->subject(env("COMPANY", "TOPMORA SHOP")." - Renouvellement de vos services");
                $message->to($customer->owner->email, $customer->owner->name);
                $message->from(env("contact", "manager@topmora.com"), env("COMPANY", "TOPMORA SHOP"));
                $message->bcc(env("contact", "manager@topmora.com"));
            });
        }
    }
}
