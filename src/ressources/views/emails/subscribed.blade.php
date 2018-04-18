<p>L'Officiel des Appels d'Offre OAO<br/>
Immeuble ASSIST Ivandry<br/>
101 Antanananarivo<br/>
Madagascar</p>
<p>{{$user->companies()->first()->nom or $user->name}}<br/>
{!!$user->companies()->first()->completeAddress or $user->completeAddress!!}
</p>

<p>Le {{$payment->created_at}}</p>
<p>Bonjour,</p>
@foreach($invoice->order->items as $item)
{!!app("rywpblog")->content($item->sellable->sellable->pack->offer->wpblog_url."/welcome")!!}
@endforeach
<p>Vous pouvez consulter votre facture {{$payment->order_reference}} via le lien suivant en cliquant dessus ou en le recopiant:</p>
<p><a href="{{action("\Ry\Appeldoffres\Http\Controllers\UserController@invoiceDetail", ["invoice" => $invoice])}}">{{action("\Ry\Appeldoffres\Http\Controllers\UserController@invoiceDetail", ["invoice" => $invoice])}}</a></p>
<p>Pour la version pdf</p>
<p><a href="{{action("\Ry\Appeldoffres\Http\Controllers\UserController@download", ["invoice" => $invoice])}}">{{action("\Ry\Appeldoffres\Http\Controllers\UserController@download", ["invoice" => $invoice])}}</a></p>
<p>Cordialement</p>
<p>Votre Service Client OAO<br/>
Lun - Vend : 08h - 20h | Samedi : 09h - 17h<br/>
034 96 545 54</p>
