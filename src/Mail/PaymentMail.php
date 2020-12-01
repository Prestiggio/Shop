<?php

namespace Ry\Shop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use App\User;
use Twig\Loader\ArrayLoader;
use Twig\Environment;
use Ry\Centrale\Models\Push;

class PaymentMail extends Mailable
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
        $cart = $data['cart'];
        $this->payload = $cart;
        $site = app("centrale")->getSite();
        $media = $template->medias()->where('title', '=', App::getLocale())->first();
        $author = $data['author'];
        $this->final_recipient = $author;
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
        $subject = $twig->render("subject", $data);
        $this->subject($subject);
        $author->notify([
            'cart_id' => $cart->id,
            'href' => app("centrale")->buildBuyerUrl(__('/marketplace/cart?id=:id', ['id' => $cart->id])),
            'text' => $subject,
            'category' => 'marketplace',
            'icon' => 'icon-info text-success'
        ]);
        $this->content = $twig->render("content", $data);
        
        $mpdf = new Mpdf([
            'debug' => env('APP_DEBUG'),
            'defaultCssFile' => public_path('css/pdf.css'),
            'tempDir' => storage_path('tmp')
        ]);
        $mpdf->SetTitle(__('Facture'));
        $formatter = new \NumberFormatter('fr-FR', \NumberFormatter::DECIMAL);
        $currency_formatter = new \NumberFormatter('fr-FR', \NumberFormatter::CURRENCY);
        $view = view("ryshop::payment.pdf", [
            "data" => $cart,
            "author" => $author,
            "f" => $formatter,
            "f2" => $currency_formatter,
            "vat" => app("centrale")->getVat(),
            "currency" => app("centrale")->getCurrency(),
            "site" => app("centrale")->getSite()
            ]);
        $mpdf->WriteHTML($view->render());
        $filename = 'cart' . $cart->id . '.pdf';
        $this->attachData($mpdf->Output($filename, Destination::STRING_RETURN), $filename);
        $data['user'] = $author;
        $this->to = [['address' => $twig->render("recipient_email", $data), 'name' => $twig->render("recipient_name", $data)]];
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
        $operation = $this->payload;
        $content = $this->content;
        $recipient = $this->final_recipient;
        return $this->html($this->content)->withSwiftMessage(function(\Swift_Message $m)use($operation,$content,$recipient){
            $cid = $m->getId();
            $push = new Push();
            $push->user_id = $recipient->id;
            $push->object_type = get_class($operation);
            $push->object_id = $operation->id;
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
