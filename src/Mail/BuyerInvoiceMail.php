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

class BuyerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $content, $payload, $final_recipient;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data)
    {
        $site = app("centrale")->getSite();
        $media = $template->medias()->where('title', '=', App::getLocale())->first();
        $invoice = $data['invoice'];
        $author = $data['author'];
        $this->final_recipient = $author;
        $this->payload = $invoice;
        $setup = json_decode($media->descriptif);
        $content = Storage::disk('local')->get($media->path);
        $content = str_replace("</twig>", "}}", preg_replace("/\<twig macro=\"([^\"]+)\"\>[^\<]*/", '{{$1', $content));
        $loader = new ArrayLoader([
            'subject' => isset($setup->subject) ? $setup->subject : $setup->subject,
            'signature' => isset($setup->signature) ? $setup->signature : $setup->signature,
            'content' => $content,
            'recipient_email' => isset($template->nsetup['recipient']['email'])?$template->nsetup['recipient']['email']:$author->email,
            'recipient_name' => isset($template->nsetup['recipient']['name'])?$template->nsetup['recipient']['name']:$author->name
        ]);
        $twig = new Environment($loader);
        $twig->addGlobal("site", $site->nsetup);
        $subject = $twig->render("subject", [
            'invoice' => $invoice
        ]);
        $this->subject($subject);
        $author->notify([
            'invoice_id' => $invoice->id,
            'href' => $invoice->buyer_url,
            'text' => $subject,
            'category' => 'marketplace',
            'icon' => 'icon-info text-success'
        ]);
        $this->content = $twig->render("content", $data);
        $this->attachData($invoice->order->pdf('S'), $invoice->nsetup['serial'].'.pdf');
        $data['user'] = $invoice->nsetup['billing_address'];
        $this->to = [['address' => $twig->render("recipient_email", $data), 'name' => $twig->render("recipient_name", $data)]];
        if($author->email!=$invoice->nsetup['billing_address']['email']) {
            $this->cc($author->email, $author->name);
        }
        if(!$site->nsetup['general']['email']) {
            $this->to = [['address' => isset($site->nsetup['contact']['email']) ? $site->nsetup['contact']['email'] : env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
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
        $recipient = $this->final_recipient;
        return $this->html($this->content)->withSwiftMessage(function(\Swift_Message $m)use($invoice,$content,$recipient){
            $cid = $m->getId();
            $push = new Push();
            $push->user_id = $recipient->id;
            $push->object_type = get_class($invoice);
            $push->object_id = $invoice->id;
            $push->content = $content;
            $push->confirm_reading = false;
            $push->channel = 'email';
            $push->cid = $cid;
            $push->nsetup = [
                'token' => str_random(60)
            ];
            $push->save();
        });
    }
}
