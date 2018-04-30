<p>{{env("SHOP")}}</p>
{!!$shop->completeAddress!!}
<p>{{$user->companies()->first()->nom}}<br/>
{!!$user->companies()->first()->completeAddress!!}
</p>

<p>Le {{$payment->created_at}}</p>
<p>Bonjour,</p>
<p>Vous pouvez consulter votre facture {{$payment->order_reference}} via le lien suivnat en cliquant dessus ou en le recopiant:</p>
<p><a href="{{action("\Ry\Appeldoffres\Http\Controllers\UserController@invoiceDetail", ["invoice" => $invoice])}}">{{action("\Ry\Appeldoffres\Http\Controllers\UserController@invoiceDetail", ["invoice" => $invoice])}}</a></p>
<p>Pour la version pdf</p>
<p><a href="{{action("\Ry\Appeldoffres\Http\Controllers\UserController@download", ["invoice" => $invoice])}}">{{action("\Ry\Appeldoffres\Http\Controllers\UserController@download", ["invoice" => $invoice])}}</a></p>
<p>Cordialement</p>
<p>Votre Service Client {{env("SHOP")}}<br/>
Lun - Vend : 08h - 20h | Samedi : 09h - 17h<br/>
{!!$shop->completeContacts!!}</p>
