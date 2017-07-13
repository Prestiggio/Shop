<style type="text/css">
.fleft {
	float: left;
}
.fright {
	float: right;
}
.clear {
	clear: both;
}
.w3 {
	width: 33%;
}
.bordered {
	border: 0.4mm solid #000000;
	padding: 3mm;
	margin: 2mm;
}
.table {
	border-collapse: collapse;
	width: 90%;
	border-spacing: 0;
	border: 0.2mm solid #000000;
	vertical-align: top;
}
.table-bordered td, .table-bordered th {
	border: 0.1mm solid #000000;
	padding: 2mm;
}
.underline {
	text-decoration: underline;
}
.text-right{
	text-align: right;
}
.adresse {
	padding: 10mm 0;
}

</style>
<page orientation="p" format="A4" footer="page;date;time" backtop="20mm" backbottom="60mm">
	<page_header>
		@if($row->order->cart->customer->owner->thumb)
		<img alt="{{$row->order->cart->customer->owner->name}}" src="{{$row->order->cart->customer->owner->thumb}}" class="fleft">
		@endif
		@if($row->order->shop->thumb)
		<img alt="{{$row->order->shop->name}}" src="{{$row->order->shop->thumb}}" class="fright">
		@endif
		<div class="clear"></div>
	</page_header>
	<page_footer>
		<div class="fleft">
			
		</div>
		<div class="fright gray">
			
		</div>
		<div class="clear"></div>
	</page_footer>
	<table class="table">
		<tr>
			<td class="w3">				
				<p class="underline">@lang("rycart::overall.to")</p>
				<p>{{$row->order->cart->customer->owner->name}}<br/>
				@if($row->order->cart->customer->owner->profile->adresse)
				{!! $row->order->cart->customer->owner->profile->completeAddress !!}
				@endif
				</p>
				<hr/>
				@if($row->order->payment!="")
				<p><span class="underline">Modalités : </span>{{$row->order->payment}}</p>
				@endif
				<p><span class="underline">Notes : </span>{{$row->note}}</p>
			</td>
			<td class="w3">
				<h4>Facture n°{{$row->id}}</h4>
				@if($row->order->reference!="")
				<div class="bordered">
					{{$row->order->reference}}
				</div>
				@endif
				<div class="bordered">
					Date : {{$row->order->invoice_date}}
				</div>
				<div class="bordered">
					Echéance : {{$row->order->delivery_date}}
				</div>
			</td>
			<td class="w3">
				<p>Beneficiaire : {{$row->order->shop->name}}<br/>
				<div class="adresse">
					{!! $row->order->shop->owner->completeAddress !!}
				</div>
				@if($row->order->shop->owner->rib)
				IBAN : {{$row->order->shop->owner->rib->iban}}<br/>
				BIC : {{$row->order->shop->owner->rib->bic}}<br/>
				SWIFT : {{$row->order->shop->owner->rib->swift}}
				@endif
				</p>
				<hr/>
				{!! $row->order->shop->owner->completeContacts !!}
				<div class="bordered">
					Service : {{$row->shop_adresse}}
				</div>
			</td>
		</tr>
	</table>
	<br/>
	<br/>
	<table class="table table-bordered">
		<tr>
			<th style="width: 10%;">Quantité</th>
			<th style="width: 40%">Description</th>
			<th style="width: 20%;">Unité</th>
			<th style="width: 10%;">Prix unitaire ({{$row->order->currency->iso_code}})</th>
			<th style="width: 10%;">Montant ({{$row->order->currency->iso_code}})</th>
		</tr>
		<?php $i = 1; ?>
		@foreach($row->order->items as $item)
		<tr>
			<td class="text-right">{{$item->quantity}}</td>
			<td>{{$item->sellable_name}}</td>
			<td>{{$item->unit}}</td>
			<td class="text-right">{{$item->unit_price_tax_incl}}</td>
			<td class="text-right">{{$item->total_price_tax_incl}}</td>
		</tr>
		<?php $i++; ?>
		@endforeach
		<tr>
			<td colspan="3" class=""></td>
			<td class="text-right"><strong>TOTAL</strong> ({{$row->order->currency->iso_code}})</td>
			<td class="text-right"><strong>{{$row->total_products}}</strong></td>
		</tr>
		<tr>
			<td colspan="3" class=""></td>
			<td class="text-right">Unité monétaire</td>
			<td class="text-right">{{$row->order->currency->iso_code}}</td>
		</tr>
	</table>
	<div class="bordered"><strong>Conditions: </strong>Payable</div>
</page>