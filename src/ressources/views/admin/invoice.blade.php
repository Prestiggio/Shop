@extends("ryrealestate::layouts.page2")

@section("main")
<div layout="row">
	<div flex="50" class="md-padding">
		<md-content class="affix">
			<table style="width:100%">
			  <tr>    
	    			<th>id</th>    
	    			<th>id commande</th>        
	    			<th>total payé TTC</th>    
	    			<th>total payé HT</th>    
	    			<th>total</th>    
	    			<th>total TTC</th>
			  </tr>
			  <tbody ng-repeat="row in data.rows">
			 	<tr>  	
	    			<td>@{{row.id}}</td>  	
	    			<td>@{{row.order_id}}</td>  	
	    			<td>@{{row.total_paid_tax_incl}}</td>  	
	    			<td>@{{row.total_paid_tax_excl}}</td>  	
	    			<td>@{{row.total_products}}</td>  	
	    			<td>@{{row.total_products_wt}}</td>
			 	</tr>
			 	<tr>
			    	<td colspan="6" class="text-right">
			    		<md-button ng-click="data.focus=row">Modifier</md-button>
			    		<md-button href="@{{row.detail_url}}">Voir détail</md-button>
			    		<md-button ng-click="deleteinvoice(row)">Supprimer</md-button>
			    	</td>
			  	</tr>
			  </tbody>
			</table>
		</md-content>
	</div>
	<div flex="50">
			<div class="text-right">
				<md-button ng-click="reset()">Nouveau</md-button>
			</div>
			<form novalidate name="frm_invoice" ng-submit="frm_invoice.$valid && submitinvoice()" layout="column" class="md-padding">
				<md-datepicker ng-model="data.focus.delivery_date" md-placeholder="Date de livraison" required></md-datepicker>				
				<md-input-container class="md-block">
				 	<label>Totale réduction TTC</label>
				 	<input type="text" ng-model="data.focus.total_discounts_tax_incl" name="invoice_total_discounts_tax_incl" required>
				 	<div ng-messages="frm_invoice.invoice_total_discounts_tax_incl.$error">
				 		<div ng-message="required">Vous devez renseigner la totale réduction TTC</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Totale réduction HT</label>
				 	<input type="text" ng-model="data.focus.total_discounts_tax_excl" name="invoice_total_discounts_tax_excl" required>
				 	<div ng-messages="frm_invoice.invoice_total_discounts_tax_excl.$error">
				 		<div ng-message="required">Vous devez renseigner la totale réduction HT</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total payé TTC</label>
				 	<input type="text" ng-model="data.focus.total_paid_tax_incl" name="invoice_total_paid_tax_incl" required>
				 	<div ng-messages="frm_invoice.invoice_total_paid_tax_incl.$error">
				 		<div ng-message="required">Vous devez renseigner le total payé TTC</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total payé HT</label>
				 	<input type="text" ng-model="data.focus.total_paid_tax_excl" name="invoice_total_paid_tax_excl" required>
				 	<div ng-messages="frm_invoice.invoice_total_paid_tax_excl.$error">
				 		<div ng-message="required">Vous devez renseigner le total payé HT</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total</label>
				 	<input type="text" ng-model="data.focus.total_products" name="invoice_total_products" required>
				 	<div ng-messages="frm_invoice.invoice_total_products.$error">
				 		<div ng-message="required">Vous devez renseigner le total</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total TTC</label>
				 	<input type="text" ng-model="data.focus.total_products_wt" name="invoice_total_products_wt" required>
				 	<div ng-messages="frm_invoice.invoice_total_products_wt.$error">
				 		<div ng-message="required">Vous devez renseigner le total TTC</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total emballé TTC</label>
				 	<input type="text" ng-model="data.focus.total_wrapping_tax_incl" name="invoice_total_wrapping_tax_incl" required>
				 	<div ng-messages="frm_invoice.invoice_total_wrapping_tax_incl.$error">
				 		<div ng-message="required">Vous devez renseigner le total emballé TTC</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Total emballé HT</label>
				 	<input type="text" ng-model="data.focus.total_wrapping_tax_excl" name="invoice_total_wrapping_tax_excl" required>
				 	<div ng-messages="frm_invoice.invoice_total_wrapping_tax_excl.$error">
				 		<div ng-message="required">Vous devez renseigner le total emballé HT</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Service</label>
				 	<input type="text" ng-model="data.focus.shop_adresse" name="invoice_shop_adresse">
				 	<div ng-messages="frm_invoice.invoice_shop_adresse.$error">
				 		<div ng-message="required">Vous devez renseigner le Service</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Note</label>
				 	<input type="text" ng-model="data.focus.note" name="invoice_note">
				 	<div ng-messages="frm_invoice.invoice_note.$error">
				 		<div ng-message="required">Vous devez renseigner la note</div>
				 	</div>
				</md-input-container>
				<md-button type="submit" class="md-raised md-primary" ng-disabled="loading || frm_invoice.$pending">Enregistrer</md-button>
			</form>
	</div>
</div>
@stop

@section("script")
<script type="text/javascript">
function main($scope, $http, $window) {
	$scope.data = {
		rows : {!!$rows!!},
		focus : {}
	};

	for(var d in $scope.data.rows) {
		$scope.data.rows[d].delivery_date = $scope.data.rows[d].delivery_date.toDate();
	}

	$scope.reset = function(){
		$scope.data.focus = {};
	};

	$scope.submitinvoice = function(){
		$http.post("{{action("\Ry\Shop\Http\Controllers\AdminController@postInvoice")}}", $scope.data.focus).then(function(){
			$window.location.reload();
		});
	};

	$scope.deleteinvoice = function(row){
		$http.delete("{{action("\Ry\Shop\Http\Controllers\AdminController@deleteInvoice")}}", row).then(function(){
			$window.back();
		});
	}
}
main.$inject = ["$scope", "$http", "$window"];
</script>
@stop