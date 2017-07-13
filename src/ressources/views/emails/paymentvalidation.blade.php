De {{$user->name}}<br/>
Phone : {{$phone}}<br/>
ID : #{{$user->id}}<br/>
Panier : <a href="{{$cart->admin_url}}">{{$cart->admin_url}}</a><br/>
Mode de paiement : {{$invoice->order->payment}}<br/>
Facture : <a href="{{$invoice->admin_url}}">{{$invoice->admin_url}}</a>
