@extends("ryrealestate::layouts.page2")

@section("main")
<div layout="row">
	<div flex="50" class="md-padding">
		<table style="width:100%">
		  <tr>    
    			<th>id</th>    
    			<th>identifiant de la boutique</th>    
    			<th>identifiant de l'adresse de livraison</th>    
    			<th>identifiant de l'adresse de facturation</th>    
    			<th>identifiant de la devise</th>    
    			<th>identifiant du compte client</th>    
    			<th>recyclabilité</th>
		  </tr>
		  <tbody ng-repeat="row in data.rows">
		 	<tr>  	
    			<td>@{{row.id}}</td>  	
    			<td>@{{row.shop_id}}</td>  	
    			<td>@{{row.delivery_adresse_id}}</td>  	
    			<td>@{{row.billing_adresse_id}}</td>  	
    			<td>@{{row.currency_id}}</td>  	
    			<td>@{{row.customer_id}}</td>  	
    			<td>@{{row.recyclable}}</td>
		 	</tr>
		 	<tr>
		    	<td colspan="3" class="text-right">
		    		<md-button ng-click="data.focus=row">Modifier</md-button>
		    		<md-button href="@{{row.detail_url}}">Voir détail</md-button>
		    		<md-button ng-click="deletecart(row)">Supprimer</md-button>
		    	</td>
		  	</tr>
		  </tbody>
		</table>
	</div>
	<div flex="50">
		<md-content class="affix">
			<div class="text-right">
				<md-button ng-click="reset()">Nouveau</md-button>
			</div>
			<form novalidate name="frm_cart" ng-submit="frm_cart.$valid && submitcart()" layout="column" class="md-padding">
				<ng-form ng-repeat="item in data.focus.items">
					<div>@{{item.sellable.title}}</div>
					<md-input-container class="md-block">
					 	<label>Quantité</label>
					 	<input type="text" ng-model="item.quantity" name="cart_sellable_quantity" required>
					 	<div ng-messages="frm_cart.cart_sellable_quantity.$error">
					 		<div ng-message="required">Vous devez renseigner le quantité</div>
					 	</div>
					</md-input-container>				
					<md-input-container class="md-block">
					 	<label>Unité</label>
					 	<input type="text" ng-model="item.unit" name="cart_sellable_unit">
					 	<div ng-messages="frm_cart.cart_sellable_unit.$error">
					 		<div ng-message="required">Vous devez renseigner l'unité</div>
					 	</div>
					</md-input-container>
				</ng-form>
				<md-button type="submit" class="md-raised md-primary" ng-disabled="loading || frm_cart.$pending">Enregistrer</md-button>
			</form>
		</md-content>
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

	$scope.reset = function(){
		$scope.data.focus = {};
	};

	$scope.submitcart = function(){
		$http.post("{{action("\Ry\Shop\Http\Controllers\AdminController@postCart")}}", $scope.data.focus).then(function(){
			$window.location.reload();
		});
	};

	$scope.deletecart = function(row){
		$http.delete("{{action("\Ry\Shop\Http\Controllers\AdminController@deleteCart")}}", row).then(function(){
			$window.location.reload();
		});
	}
}
main.$inject = ["$scope", "$http", "$window"];
</script>
@stop