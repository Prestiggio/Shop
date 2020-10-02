<?php

namespace Ry\Shop\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ry\Profile\Models\NotificationTemplate;
use Ry\Shop\Models\OrderInvoice;
use Ry\Shop\Mail\BuyerInvoiceMail;
use Ry\Shop\Mail\SellerInvoiceMail;

class InvoiceMailing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * 
     * @var OrderInvoice
     */
    private $invoice;
    
    private $sellers;
    private $buyers;
    
    private $site_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderInvoice $invoice, $sellers, $buyers, $site_id)
    {
        $this->invoice = $invoice;
        $this->sellers = $sellers;
        $this->buyers = $buyers;
        $this->site_id = $site_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    { 
        app("centrale")->setSite($this->site_id);
        $templates = NotificationTemplate::whereHas("alerts", function($q){
            $q->whereCode('ryshop_buyer_invoice');
        })
        ->where("channels", "LIKE", '%MailSender%')->get();
        if($templates->count()==0) {
            throw new \Exception(__("Aucun moyen de notifier les utilisateurs. Ajouter la template d'email associé à l'évènement ryshop_buyer_invoice"), 500);
        }
        foreach($this->buyers as $user) {
            foreach($templates as $template) {
                Mail::send(new BuyerInvoiceMail($template, [$user, [
                    'invoice' => $this->invoice,
                    'user' => $user
                ]]));
            }
        }
        $templates = NotificationTemplate::whereHas("alerts", function($q){
            $q->whereCode('ryshop_seller_invoice');
        })
        ->where("channels", "LIKE", '%MailSender%')->get();
        if($templates->count()==0) {
            throw new \Exception(__("Aucun moyen de notifier les utilisateurs. Ajouter la template d'email associé à l'évènement ryshop_seller_invoice"), 500);
        }
        foreach($this->sellers as $user) {
            foreach($templates as $template) {
                Mail::send(new SellerInvoiceMail($template, [$user, [
                    'invoice' => $this->invoice,
                    'user' => $user
                ]]));
            }
        }
    }
}
