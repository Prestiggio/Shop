<?php use Carbon\Carbon; ?>
<h2>
	@lang("Facture") : <span
		class="display-4 text-primary font-weight-bold"><?php echo $data->id; ?></span>
</h2>
<div class="my-4">
	<table class="table">
		<tbody>
			<tr>
				<td>
					<h3 class="text-secondary">@lang("Livraison")</h3><br/>
					<table class="table table-align-top">
						<tbody>
							<tr>
								<th class="text-right py-0">@lang("Destinataire") :</th>
								<td class="py-0">{{$data->customer->facturable->warehouses->first()->name}}</td>
							</tr>
							<tr>
								<th class="text-right py-0">@lang("Contact") :</th>
								<td class="py-0">{{$data->customer->facturable->warehouses->first()->users->first()->profile->gender_label}} {{$data->customer->facturable->warehouses->first()->users->first()->profile->firstname}} {{$data->customer->facturable->warehouses->first()->users->first()->profile->lastname}}</td>
							</tr>
							<tr>
								<th class="text-right py-0">@lang("Adresse") :</th>
								<td class="py-0">
									{{$data->customer->facturable->warehouses->first()->adresse->raw}}<br/>
									{{$data->customer->facturable->warehouses->first()->adresse->ville->cp}}<br/>
									{{$data->customer->facturable->warehouses->first()->adresse->ville->nom}}-{{$data->customer->facturable->warehouses->first()->adresse->ville->country->nom}}
								</td>
							</tr>
						</tbody>
					</table>
				</td>
				<td class="border-left">
					<h3 class="text-secondary">@lang("Facturation")</h3><br/>
					<table class="table table-align-top">
						<tbody>
							<tr>
								<th class="text-right py-0">@lang("Facturé à") :</th>
								<td class="py-0">{{$data->customer->facturable->name}}</td>
							</tr>
							<tr>
								<th class="text-right py-0">@lang("Contact") :</th>
								<td class="py-0">{{$author->profile->gender_label}} {{$author->profile->firstname}} {{$author->profile->lastname}}</td>
							</tr>
							<tr>
								<th class="text-right py-0">@lang("Adresse") :</th>
								<td class="py-0">
									{{$data->customer->facturable->adresse->raw}}<br/>
									{{$data->customer->facturable->adresse->ville->cp}}<br/>
									{{$data->customer->facturable->adresse->ville->nom}}-{{$data->customer->facturable->adresse->ville->country->nom}}
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<table class="table table-bordered font-11 table-bc my-4">
	<tbody>
		<tr class="bg-light">
			<th>@lang("Désignation")</th>
			<th class="text-center" width="110">@lang("UCI")</th>
			<th class="text-center" width="110">@lang("Qté UCI")</th>
			<th class="text-center" width="110">@lang("Poids total")<span
				class="font-weight-normal">(@lang("kg"))</span></th>
			<th class="text-center" width="110">@lang("Prix UCI") <span
				class="font-weight-normal">@lang(":currency_symbol HT", ['currency_symbol' => $currency->symbol])</span></th>
			<th class="text-center" width="110">@lang("Total :currency_symbol HT", ['currency_symbol' => $currency->symbol])</th>
		</tr>
		<?php $total_ht = 0; ?>
		@foreach($data->items as $item)
		<tr>
			<td class="align-middle"><h3>{{$item->sellable->product->name}}</h3>
			@lang("Réf") : <strong
				class="text-danger">{{$item->sellable->nsetup['reference']}}</strong>
			<div>
				<img
						src="{{$item->sellable->product->medias->first()->fullpath}}" style="max-width: 40mm;"/>
				<div class="col-md-6">
					@foreach($item->sellable->visible_specs as $spec)
					@if($spec['functions']!='uci')
					<strong>{{$spec['label']}}</strong> : {{$spec['option']}}<br>
					@endif
					@endforeach
				</div>
			</div></td>
			<td class="text-center align-middle">
			<?php 
			$uci = 'Unité';
			$ucis = array_filter($item->sellable->visible_specs, function($it){
			    return $it['functions'] == 'uci';
			});
		    if(count($ucis)>0) {
		        $uci = array_values($ucis)[0]['option'];
		    }
		    echo $uci;
			?>
			</td>
			<td class="text-center align-middle">{{$f->format($item->quantity)}}</td>
			<td class="text-center align-middle">
			<?php 
			$uci_weight = 1;
			$uci_weights = array_filter($item->sellable->visible_specs, function($it){
			    return $it['functions'] == 'uci_weight';
			});
		    if(count($uci_weights)>0) {
		        $uci_weight = array_values($uci_weights)[0]['value'];
		    }
		    echo $f->format($item->quantity * $uci_weight);
			?>
			</td>
			<td class="text-center font-weight-bold align-middle">
			<?php
			$selected_price = array_filter($item->nsetup['prices'], function($it)use($item){
			    return $it['shop_id'] == $item->nsetup['shop_id'];
			});
			    echo $f2->format($selected_price[0]['unit_price_commissionned']);
			?>
			</td>
			<td class="text-center align-middle font-weight-bold">
			<?php 
			echo $f2->format($selected_price[0]['commission_factor']*$item->nsetup['unit_price']*$item->quantity);
			$total_ht += ($selected_price[0]['commission_factor']*$item->nsetup['unit_price']*$item->quantity);
			?>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<table class="table table-align-top">
	<tbody>
		<tr>
			<td rowspan="4">
				<div>
					<strong>UCI</strong> = @lang("Unité de conditionnement Intermédiaire")
				</div>
				<div>
					<strong>PCB</strong> = @lang("Vendu par Combien")
				</div>
			</td>
			<th class="text-right">
				@lang("Total HT") : 
			</th>
			<th>
				{{$f2->format($total_ht)}}
			</th>
		</tr>
		<tr>
			<th class="text-right">
				@lang("Frais de port") :
			</th>
			<th><?php 
			$delivery = 0;
			foreach($data['nsetup']['shop'] as $shop){
			    $delivery += $shop['delivery'];
			}
			echo $f2->format($delivery);
			$total_ht += $delivery;
			?></th>
		</tr>
		<tr>
			<th class="text-right">
				@lang("TVA (:n%)", ["n" => $vat*100]) :
			</th>
			<th>
				{{$f2->format($total_ht*$vat)}}
			</th>
		</tr>
		<tr>
			<th class="text-right">
				@lang("Total TTC") :
			</th>
			<th class="display-4">
				{{$f2->format($total_ht*(1+$vat))}}
			</th>
		</tr>
	</tbody>
</table>
<br/>
<h3>@lang("Réglable par virement bancaire aux coordonnées ci-dessous") : </h3>
<table class="table table-bordered my-4">
	<tbody>
		<tr>
			<th class="text-uppercase" colspan="4">@lang("Relevé d'identité bancaire")</th>
			<th>@lang("Titulaire du compte")</th>
		</tr>
		<tr>
			<th>@lang("Banque")</th>
			<th>@lang("Guichet")</th>
			<th>@lang("Compte")</th>
			<th>@lang("Clé")</th>
			<td rowspan="4">
				<strong>{{$site->nsetup['siege']['societe']}}</strong><br/>
				{{$site->nsetup['siege']['adresse']}}<br/>
				{{$site->nsetup['siege']['code']}} {{$site->nsetup['siege']['ville']}}
			</td>
		</tr>
		<tr>
			<td>{{$site->nsetup['bancaire']['banque']}}</td>
			<td>{{$site->nsetup['bancaire']['guichet']}}</td>
			<td>{{$site->nsetup['bancaire']['compte']}}</td>
			<td>{{$site->nsetup['bancaire']['cle']}}</td>
		</tr>
		<tr>
			<th colspan="4">@lang("IBAN")</th>
		</tr>
		<tr>
			<td colspan="4">{{$site->nsetup['bancaire']['iban']}}</td>
		</tr>
	</tbody>
</table>