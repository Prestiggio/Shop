@extends("ryrealestate::layouts.page2")

@section("script")
<script type="text/javascript">
function main() {
	
}
</script>
@stop

@section("main")
<style type="text/css">
.ry-cart-table {
	width: 100%;
	border-collapse: collapse;
}
.ry-cart-table td, .ry-cart-table th {
	border: 1px solid #333;
}
.ry-cart-table td.no-border-sw {
	border-bottom: none;
	border-left: none;
}
</style>
<div layout="row" class="md-padding" layout-align="space-between start">
	<div>
		<h1>FACTURE n°{{$row->id}}</h1>
		<div>
			<strong>Date : </strong>{{$row->order->invoice_date}}
		</div>
		<div>
			<strong>Echéance : </strong>{{$row->order->delivery_date}}
		</div>
		@if($row->order->cart->customer->owner->thumb)
		<img alt="{{$row->order->cart->customer->owner->name}}" src="{{$row->order->cart->customer->owner->thumb}}">
		@endif
		<p>@lang("rycart::overall.to") <strong>{{$row->order->cart->customer->owner->name}}</strong></p>
		@if($row->order->cart->customer->owner->profile && $row->order->cart->customer->owner->profile->adresse)
		<div>
			{!! $row->order->cart->customer->owner->profile->completeAddress !!}
		</div>
		@endif
		<hr/>
		@if($row->order->reference!="")
		<div><strong>Référence</strong> : {{$row->order->reference}}</div>
		@endif
		@if($row->order->payment!="")
		<div><strong>Modalités</strong> : {{$row->order->payment}}</div>
		@endif
	</div>
	<div class="text-right md-padding ry-cart-target">
		@if($row->order->shop->thumb)
		<img alt="{{$row->target->name}}" src="{{$row->order->shop->thumb}}">
		@endif
		<h3>{{$row->order->shop->name}}</h3>
		<div>
			{!! $row->order->shop->owner->completeAddress !!}
		</div>
		<div>
			{!! $row->order->shop->owner->completeContacts !!}
		</div>
		@if($row->shop_adresse!="")
		<div>
			<strong>Service</strong> : {{$row->shop_adresse}}
		</div>
		@endif
		@if($row->order->shop->owner->rib)
		<div><strong>IBAN : </strong> {{$row->order->shop->owner->rib->iban}}</div>
		<div><strong>BIC : </strong> {{$row->order->shop->owner->rib->bic}}</div>
		<div><strong>SWIFT : </strong> {{$row->order->shop->owner->rib->swift}}</div>
		@endif
	</div>
</div>
<br/>
<table class="ry-cart-table">
	<thead>
		<th>N°</th>
		<th>designation</th>
		<th>quantité</th>
		<th>unité</th>
		<th>prix unitaire ({{$row->order->currency->iso_code}})</th>
		<th>montant ({{$row->order->currency->iso_code}})</th>
	</thead>
	<tbody>
		<?php $i = 1; ?>
		@foreach($row->order->items as $item)
		<tr>
			<td class="text-center">{{$i}}</td>
			<td>{{$item->sellable_name}}</td>
			<td class="text-right">{{$item->quantity}}</td>
			<td class="text-center">{{$item->unit}}</td>
			<td class="text-right">{{number_format($item->unit_price_tax_incl, 2, ",", ".")}}</td>
			<td class="text-right">{{number_format($item->total_price_tax_incl, 2, ",", ".")}}</td>
		</tr>
		<?php $i++; ?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4" class="no-border-sw"></td>
			<td class="text-right no-border-sw"><strong>TOTAL</strong> ({{$row->order->currency->iso_code}})</td>
			<td class="text-right">{{number_format($row->total_products, 2, ",", ".")}}</td>
		</tr>
	</tfoot>
</table>
<br/>
<div class="text-right">
	<md-button class="md-raised" href="{{action("\Ry\Shop\Http\Controllers\UserController@download", ["invoice" => $row])}}">Télécharger</md-button>
</div>
@if($row->note!="")
<div><strong>Notes</strong> : {{$row->note}}</div>
@endif

@stop