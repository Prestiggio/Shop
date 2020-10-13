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
use App\User;

class InvoiceMailing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * 
     * @var OrderInvoice
     */
    private $invoice;
    
    private $site_id;
    
    private $author;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderInvoice $invoice, User $author, $site_id)
    {
        $this->invoice = $invoice;
        $this->site_id = $site_id;
        $this->author = $author;
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
        
        foreach($templates as $template) {
            Mail::send(new BuyerInvoiceMail($template, [
                'invoice' => $this->invoice,
                'author' => $this->author
            ]));
        }
        $templates = NotificationTemplate::whereHas("alerts", function($q){
            $q->whereCode('ryshop_seller_invoice');
        })
        ->where("channels", "LIKE", '%MailSender%')->get();
        if($templates->count()==0) {
            throw new \Exception(__("Aucun moyen de notifier les utilisateurs. Ajouter la template d'email associé à l'évènement ryshop_seller_invoice"), 500);
        }
        foreach($templates as $template) {
            Mail::send(new SellerInvoiceMail($template, [
                'invoice' => $this->invoice
            ]));
        }
    }
}
