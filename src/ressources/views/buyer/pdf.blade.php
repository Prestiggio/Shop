<page orientation="p" format="A4" footer="page;date;time" backtop="20mm" backbottom="60mm">
	<page_header>
		
	</page_header>
	<page_footer>
		
	</page_footer>
	<table class="table table-align-top">
		<tr>
			<td>
				<h2 class="display-4">@lang("Commande n°") : <span class="display-4 text-primary font-weight-bold">{{$row->nsetup['serial']}}</span></h2>				
				<br/>
				<strong>@lang("Date") : </strong>{{$row->created_at->format('d/m/Y')}}
				@if($row->payment!="")
				<p><span class="underline">Modalités : </span>{{$row->payment}}</p>
				@endif
				<p><span class="underline">Notes : </span>{{$row->note}}</p>
			</td>
			<td>
				<table class="table table-align-top">
					<tbody>
						<tr>
							<th class="text-right py-0">
								@lang("Bénéficiaire") :
							</th>
							<td class="py-0">
								{{$row->shop->name}}
							</td>
						</tr>
						<tr>
							<th class="text-right py-0">
								@lang("Adresse") :
							</th>
							<td class="py-0">
								{!! $row->shop->owner->completeAddress !!}
							</td>
						</tr>
						@if($row->shop->owner->rib)
						<tr>
							<th class="text-right py-0">
								@lang("IBAN") :
							</th>
							<td class="py-0">
								{{$row->shop->owner->rib->iban}}
							</td>
						</tr>
						<tr>
							<th class="text-right py-0">
								@lang("BIC") :
							</th>
							<td class="py-0">
								{{$row->shop->owner->rib->bic}}
							</td>
						</tr>
						<tr>
							<th class="text-right py-0">
								@lang("SWIFT") :
							</th>
							<td class="py-0">
								{{$row->shop->owner->rib->swift}}
							</td>
						</tr>
						@endif
						<tr>
							<th class="text-right py-0">
								@lang("Contacts") : 
							</th>
							<td class="py-0">
								{!! $row->shop->owner->completeContacts !!}
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<br/>
	<table class="table table-bc border-left-0 table-align-top">
		<tbody>
			<tr class="bg-light">
				<td>@lang("Livraison")</td>
				<td>@lang("Facturation")</td>
			</tr>
			<tr>
				<td>
					<p><strong>{{$row->cart->nsetup['delivery_address']['name']}}</strong><br/>
    				<p>
    					{{$row->cart->deliveryAddress->raw}}<br/>
    					{{$row->cart->deliveryAddress->ville->cp}} {{$row->cart->deliveryAddress->ville->nom}}<br/>
    					{{$row->cart->deliveryAddress->ville->country->nom}}<br/>
    					@lang("Tél") : {{$row->cart->nsetup['delivery_address']['contacts']['fixe']}}
    				</p>
				</td>
				<td>
					<p><strong>{{$row->cart->nsetup['billing_address']['name']}}</strong><br/>
    				<p>
    					{{$row->cart->billingAddress->raw}}<br/>
    					{{$row->cart->billingAddress->ville->cp}} {{$row->cart->billingAddress->ville->nom}}<br/>
    					{{$row->cart->billingAddress->ville->country->nom}}<br/>
    					@lang("Tél") : {{$row->cart->nsetup['billing_address']['contacts']['fixe']}}
    				</p>
				</td>
			</tr>
		</tbody>
	</table>
	<br/>
	<table class="table table-bc border-left-0 table-align-top">
		<tbody class="table-bordered">
    		<tr class="bg-light">
    			<th style="width: 10%;">@lang("Quantité")</th>
    			<th style="width: 40%">@lang("Description")</th>
    			<th style="width: 20%;">@lang("Unité")</th>
    			<th style="width: 10%;">@lang("Prix unitaire")</th>
    			<th style="width: 10%;">@lang("Montant")</th>
    		</tr>
    		<?php $i = 1;
    		$total_ht = 0;
    		?>
    		@foreach($row->items as $item)
    		<?php 
    		$setup = $item->nsetup;
    		$price = array_values(array_filter($item->nsetup['prices'], function($it)use($setup){
    		    return $it['shop_id'] == $setup['shop_id'];
    		}))[0];
    		?>
    		<tr>
    			<td class="text-right">{{$f->format($item->quantity)}}</td>
    			<td>
    				<h3>{{$item->sellable->product->name}}</h3>
    				@lang("Réf") : <strong
    				class="text-danger">{{$item->sellable->nsetup['reference']}}</strong>
    				<div>
    				<img src="{{$item->sellable->product->medias->first()->fullpath}}" style="max-width: 40mm;"/>
    				<div class="col-md-6">
    					@foreach($item->sellable->visible_specs as $spec)
    					@if($spec['functions']!='uci')
    					<strong>{{$spec['label']}}</strong> : {{$spec['option']}}<br>
    					@endif
    					@endforeach
    				</div>
    			</div>
    			</td>
    			<td class="text-center"><?php 
    			$uci = 'Unité';
    			$ucis = array_filter($item->sellable->visible_specs, function($it){
    			    return $it['functions'] == 'uci';
    			});
    			    if(count($ucis)>0) {
    			        $uci = array_values($ucis)[0]['option'];
    			    }
    			    echo $uci;
    			?></td>
    			<td class="text-right">
    				<?php
    				echo $f2->format($item->nsetup['unit_price'] * $price['commission_factor']);
    			?>
    			</td>
    			<th class="text-right">
    			<?php 
    			$total_ht += ($item->nsetup['unit_price'] * $price['commission_factor'] * $item->quantity);
    			echo $f2->format($item->nsetup['unit_price'] * $price['commission_factor'] * $item->quantity); ?>
    			</th>
    		</tr>
    		<?php $i++; ?>
    		@endforeach
    	</tbody>
    	<tbody>
    		<tr>
    			<th colspan="4" class="text-right border-left-0">@lang("TOTAL HT")</th>
    			<td class="text-right border"><strong>{{$f2->format($total_ht)}}</strong></td>
    		</tr>
    		<tr>
    			<th colspan="4" class="text-right border-left-0">@lang("Frais de port")</th>
    			<td class="text-right border"><strong>{{$f2->format($row->nsetup['delivery'])}}</strong></td>
    		</tr>
    		<tr>
    			<th colspan="4" class="text-right border-left-0">@lang("TVA (:n%)", ["n" => $vat*100])</th>
    			<td class="text-right border"><strong>{{$f2->format(($total_ht+$row->nsetup['delivery'])*$vat)}}</strong></td>
    		</tr>
    		<tr>
    			<th colspan="4" class="text-right border-left-0 border-bottom-0"><strong>@lang("TOTAL TTC")</strong></th>
    			<td class="text-right border"><strong>{{$f2->format(($total_ht+$row->nsetup['delivery'])*(1+$vat))}}</strong></td>
    		</tr>
    	</tbody>
	</table>
	<div class="bordered"><strong>Conditions: </strong>Payable</div>
</page>