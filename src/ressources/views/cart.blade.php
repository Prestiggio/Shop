@extends("ryrealestate::layouts.page2")

@section("main")
<div class="md-padding">
	<form novalidate name="frm_cart" ng-submit="frm_cart.$valid && submitcart()" layout="column" class="md-padding">
		<div layout="row" layout-align="space-between start">
			<h3>Votre panier</h3>
			<md-button href="" class="md-raised">Changer de packs</md-button>
		</div>
		<table class="table table-bordered">
		  <tr>    
    			<th class="text-right"style="width: 90px">N°</th>    
    			<th style="width: 90px">Quantité</th>
    			<th>Désignation</th>    
    			<th>P.U</th>    
    			<th>Montant(@{{data.cart.currency.iso_code}})</th>
		  </tr>
		  <tbody>
		 	<tr ng-repeat="(rowid,row) in data.cart.items">
    			<td class="text-right"><md-button class="md-icon-button" ng-click="remove(row)" aria-label="supprimer"><i class="fa fa-minus"></i></md-button> @{{$index+1}}</td>  	
    			<td ng-if="row.multiple">
    				<input type="number" ng-model="row.quantity" class="text-right" style="width: 80%;">
    			</td>
    			<td ng-if="!row.multiple" class="text-right">
    				@{{row.quantity}}
    			</td>
    			<td>@{{row.sellable.title}}</td>  	
    			<td class="text-right">@{{row.sellable.price|sep1000}}</td>  	
    			<td class="text-right">@{{row.sellable.price*row.quantity|sep1000}}</td>
		 	</tr>
		  </tbody>
		  <tfoot>
		  	<tr>
		  		<td colspan="4" class="no-border-sw text-right">Total(@{{data.cart.currency.iso_code}})</td>
		  		<th class="text-right">@{{sum()|sep1000}}</th>
		  	</tr>
		  </tfoot>
		</table>
		<div layout="row" layout-align="space-between start">
			<md-button class="md-raised md-accent" ng-click="back()">Ajouter</md-button>
			<md-button type="submit" class="md-raised md-primary" ng-disabled="loading || frm_cart.$pending">Payer</md-button>
		</div>
	</form>
</div>
@stop

@section("script")
<script type="text/javascript">
function main($scope, $http, $window, $app, $shopping, $sessionStorage) {
	$shopping.update({!!$rows!!});
	
	$scope.data = {
			writings : $sessionStorage.writings,
			cart : $shopping.cart()
	};

	$scope.remove = function(item){
		$http.delete("{{action("\Ry\Shop\Http\Controllers\PublicController@deleteCart")}}?sid=" + item.id).then(function(){
			$shopping.remove(item);
		});
	};

	$scope.sum = function(){
		return $shopping.total();
	};

	$scope.submitcart = function(){
		$http.post("{{action("\Ry\RealEstate\Http\Controllers\UserController@postCart")}}", $scope.data).then(function(response){
			$window.location.href = response.data.redirect;
		});
	};

	$scope.back = function(){
		$window.history.back();
	};
}
main.$inject = ["$scope", "$http", "$window", "$appSetup", "$shopping", "$sessionStorage"];
</script>
@stop