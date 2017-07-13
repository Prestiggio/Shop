@extends("ryrealestate::layouts.page2")

@section("script")
<script type="text/javascript">
function main($scope, $http, $mdDialog, $window, $shopping, $sessionStorage) {
	$scope.payment = {
		packs : {!!$rows!!},
		amount : 0,
		cart : $sessionStorage.cart
	};
	
	$scope.presubmit = function(){
		$scope.loading = true;
		var message = "";
		switch($scope.payment.mode) {
			case "mobile":
				message = $scope.payment.operator + ". Veuillez acceptez le paiement";
				break;

			case "rdv":
				message = "On vous rappelera pour un rendez vous";
				break;
		}
		var d = new Date();
		$scope.payment.actiondate = d.toYMD();
		$http.post("{{action("\Ry\Shop\Http\Controllers\PublicController@postMode")}}", $scope.payment).then(function(response){
			$scope.loading = false;
			$mdDialog.show($mdDialog.alert().clickOutsideToClose(true).title('kipa.amelior.mg')
			        .textContent(message)
			        .ok('OK!')).then(function(){
			    $window.location.href = response.data.redirect;
			});
		}, function(error){
			$scope.loading = false;
		});
	}

	$scope.calculate = function(){
		$scope.payment.amount = 0;
		
		for(var i=0; i<$scope.payment.packs.length; i++) {
			if($scope.payment.packs[i].quantity)
				$scope.payment.amount += parseFloat($scope.payment.packs[i].quantity) * parseFloat($scope.payment.packs[i].sellable.price);
		}
		
		return $scope.payment.amount>0;
	};

	$scope.choisir = function(cart) {
		if(cart.sellable.price==0)
			$window.location.href = "/login";
		
		for(var i=0; i<$scope.payment.packs.length; i++) {
			delete $scope.payment.packs[i].quantity;
		}

		cart.quantity = 1;

		$scope.calculate();
	};

	$scope.abonne = function(cart){
		addToCart();
		for(var i=0; i<$scope.payment.packs.length; i++) {
			if($scope.payment.packs[i].id!=cart.id)
				delete $scope.payment.packs[i].quantity;
		}

		$scope.calculate();
	};

	$scope.addToCart = function(){
		$shopping.save().then(function(){
			$window.location.back();
		});
	};

	$scope.unfollow = function(){
		$shopping.unfollow();
		$window.history.back();
	};
}
main.$inject = ["$scope", "$http", "$mdDialog", "$window", "$shopping", "$sessionStorage"];
</script>
@stop

@section("main")
<form name="frm_payment" novalidate ng-submit="frm_payment.$valid && presubmit()" layout="column">
	<div class="text-center">
	@if(Auth::guest())
		<h2>Pour voir les détails, vous devez être membre. <md-button href="/login" class="md-raised md-primary">Inscrivez-vous gratuitement</md-button></h2>
		<p>Vous pouvez consulter toutefois les détails incognito seulement pour <strong>200 Ar</strong></p>
	@elseif(Auth::user()->isCustomer())
		<h2>Vous avez épuisé vos crédits ! <md-button href="{{action("Ry\Shop\Http\Controllers\UserController@getHistoric")}}">Voir les dépenses</md-button></h2>
	@else
		<h2>Seulement <u>200 Ar</u> pour voir plus de détails. Ci dessous les différents options que nous vous proposons</h2>
	@endif
		<table style="width: 100%" class="table-bordered">
			<thead>
				<tr>
					<th class="text-left">To View List</th>
					<th class="text-right">P.U(@{{payment.cart.currency.iso_code}})</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-right no-border-sw">Total(@{{payment.cart.currency.iso_code}})</th>
					<th class="text-right">@{{payment.cart.amount|sep1000}}</th>
				</tr>
			</tfoot>
			<tfoot>
				<tr ng-repeat="cart in payment.cart.items">
					<td><a href="@{{cart.url}}"><img ng-src="@{{cart.image}}" style="width: 40px; margin: 10px;"></a><a href="@{{cart.url}}">@{{cart.name}}</a></td>
					<td class="text-right">@{{cart.click.price}}</td>
				</tr>
			</tfoot>
		</table>
	
		<div layout="row">
			<div flex="25" class="md-padding" ng-repeat="row in payment.packs">
				<h3>@{{row.sellable.title}}</h3>
				<p>@{{row.sellable.content}}</p>
				<md-input-container ng-if="row.sellable.price > 0 && row.sellable.type=='abonnement'">
					<label>Nombre:(mois)</label>
					<input type="number" min="0" max="48" ng-model="row.quantity" ng-change="abonne(row)">
				</md-input-container>
				<md-button class="md-raised md-accent" ng-if="row.sellable.type!='abonnement'" ng-click="choisir(row)">Choisir</md-button>
			</div>
		</div>
		<md-button ng-click="unfollow()" class="md-raised md-accent">Annuler</md-button><md-button ng-click="addToCart()" class="md-raised md-primary">Poursuivre la recherche</md-button>
	</div>
	<div ng-show="payment.amount>0" class="md-padding">
		<table style="width: 100%" class="table-bordered">
			<thead>
				<tr>
					<th class="text-left">Libellé</th>
					<th class="text-right">P.U(@{{payment.cart.currency.iso_code}})</th>
					<th class="text-right">Quantité</th>
					<th class="text-right">Montant(@{{payment.cart.currency.iso_code}})</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="3" class="text-right no-border-sw">Total(@{{payment.cart.currency.iso_code}})</th>
					<th class="text-right">@{{payment.amount|sep1000}}</th>
				</tr>
			</tfoot>
			<tfoot>
				<tr ng-repeat="cart in payment.packs" ng-if="cart.quantity>0">
					<td>@{{cart.sellable.title}}</td>
					<td class="text-right">@{{cart.sellable.price|sep1000}}</td>
					<td class="text-right">@{{cart.quantity|sep1000}}</td>
					<td class="text-right">@{{(cart.quantity*cart.sellable.price)|sep1000}}</td>
				</tr>
			</tfoot>
		</table>
		<div layout-align="center center" layout="row" class="md-padding">
			<div flex="33" class="md-padding" ng-show="payment.amount<5000000">
				<h2>Mobile Banking</h2>
				<h3>Immédiat</h3>
				<md-input-container>
					<label>Numéro de téléphone</label>
					<input type="text" name="phone" ng-model="payment.owner.phone" mg-mobile-shop mg-mobile="payment"/>
				</md-input-container>
				<md-input-container>
					<md-button ng-show="frm_payment.$valid" aria-label="payer" ng-bind="payment.payment" class="md-raised md-primary" type="submit" ng-click="payment.mode='mobile'"></md-button>
				</md-input-container>
			</div>
			<div flex="33" class="md-padding" ng-show="payment.amount>=5000">
				<h2>Carte bancaire</h2>
				<h3>Immédiat</h3>
				<div rypaypal ng-model="payment" action="{{action("\Ry\Shop\Http\Controllers\PublicController@postMode")}}"></div>
			</div>
			<div flex="33" class="md-padding">
				<h2>Chèque ou espèce</h2>
				<h3>Par rendez vous</h3>
				<md-input-container>
					<label>Numéro de téléphone</label>
					<input type="text" name="rdv_phone" ng-model="payment.owner.phone" mg-mobile-shop mg-mobile="payment"/>
				</md-input-container>
				<md-input-container>
					<md-button ng-show="frm_payment.$valid" aria-label="payer" class="md-raised md-primary" type="submit" ng-click="payment.mode='rdv'">Prendre un rendez-vous</md-button>
				</md-input-container>
			</div>
		</div>
	</div>
</form>
@stop