<?php
namespace Ry\Shop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Twig\Loader\ArrayLoader;
use Twig\Environment;
use Ry\Centrale\Models\Push;

class SellerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $content, $payload;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data)
    {
        $site = app("centrale")->getSite();
        $invoice = $data['invoice'];
        $media = $template->medias()->where('title', '=', App::getLocale())->first();
        $default_media = null;
        if(!$media)
            $media = $template->medias()->first();
        elseif($media->title!=App::getLocale()) {
            $default_media = $template->medias()->where('title', '=', App::getLocale())->first();
            $default_setup = json_decode($default_media->descriptif);
        }
        $invoice = $data['invoice'];
        $this->payload = $invoice;
        $setup = json_decode($media->descriptif);
        if(!$default_media)
            $default_setup = $setup;
        $content = Storage::disk('local')->get($media->path);
        $content = str_replace("</twig>", "}}", preg_replace("/\<twig macro=\"([^\"]+)\"\>[^\<]*/", '{{$1', $content));
        $loader = new ArrayLoader([
            'subject' => isset($setup->subject) ? $setup->subject : $default_setup->subject,
            'signature' => isset($setup->signature) ? $setup->signature : $default_setup->signature,
            'content' => $content,
            'recipient_email' => $template->nsetup['recipient']['email'],
            'recipient_name' => $template->nsetup['recipient']['name']
        ]);
        $twig = new Environment($loader);
        $twig->addGlobal("site", $site->nsetup);
        $subject = $twig->render("subject", $data);
        $this->subject($subject);
        $this->content = $twig->render("content", $data);
        $this->attachData($invoice->order->sellerPdf('S'), $invoice->nsetup['serial'].'.pdf');
        if(!$site->nsetup['general']['email']) {
            $this->to = [['address' => isset($site->nsetup['contact']['email']) ? $site->nsetup['contact']['email'] : env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        else {
            foreach($invoice->seller->anyusers as $user) {
                $user->notify([
                    'invoice_id' => $invoice->id,
                    'href' => $invoice->buyer_url,
                    'text' => $subject,
                    'category' => 'marketplace',
                    'icon' => 'icon-info text-success'
                ]);
                $data['user'] = $user;
                $this->cc($twig->render("recipient_email", $data), $twig->render("recipient_name", $data));
            }
        }
        foreach($invoice->seller->anyusers as $user) {
            $user->notify([
                'invoice_id' => $invoice->id,
                'href' => $invoice->buyer_url,
                'text' => $subject,
                'category' => 'marketplace',
                'icon' => 'icon-info text-success'
            ]);
        }
        $this->from("no-reply@".env('APP_DOMAIN'), $twig->render("signature", $data));
    }
    
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $invoice = $this->payload;
        $content = $this->content;
        return $this->html($this->content)->withSwiftMessage(function(\Swift_Message $m)use($invoice,$content){
            $cid = $m->getId();
            $token = str_random(60);
            foreach($invoice->seller->anyusers as $user) {
                $push = new Push();
                $push->user_id = $user->id;
                $push->object_type = get_class($invoice);
                $push->object_id = $invoice->id;
                $push->content = $content;
                $push->confirm_reading = false;
                $push->channel = 'email';
                $push->cid = $cid;
                $push->nsetup = [
                    'token' => $token
                ];
                $push->save();
            }
        });
    }
}
