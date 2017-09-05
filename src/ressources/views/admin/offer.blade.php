@section("offer.table")
<table style="width:100%" class="table table-bordered">
		  <tr>    
    			<th>url sur wpblog</th>    
    			<th>flux</th>    
    			<th>type</th>    
    			<th>période</th>    
    			<th>prix</th>
		  </tr>
		  <tbody ng-repeat="row in data.rows">
		 	<tr>
    			<td>@{{row.wpblog_url}}</td>
    			<td>@{{row.flux}}</td>  	
    			<td>@{{row.type}}</td>  	
    			<td>@{{row.period}}</td>  	
    			<td>@{{row.price}}</td>
		 	</tr>
		 	<tr>
		    	<td colspan="5" class="text-right">
		    		<md-button ng-click="data.focus=row" class="md-raised">Modifier</md-button>
		    		<md-button ng-click="deleteoffer(row)" class="md-raised">Supprimer</md-button>
		    	</td>
		  	</tr>
		  </tbody>
		</table>
@stop

@section("offer.form")
<div class="text-right">
	<md-button ng-click="reset()">Nouveau</md-button>
</div>
<form novalidate name="frm_offer" ng-submit="frm_offer.$valid && submitoffer()" layout="column" class="md-padding">	
	<md-input-container class="md-block">
	 	<label>Url sur wpblog</label>
	 	<input type="text" ng-model="data.focus.wpblog_url" name="offer_wpblog_url" required>
	 	<div ng-messages="frm_offer.offer_wpblog_url.$error">
	 		<div ng-message="required">Vous devez renseigner l'url sur wpblog</div>
	 	</div>
	</md-input-container>				
	<md-input-container class="md-block">
	  <label>Type</label>
	  <md-select ng-model="data.focus.type" required>
	    <md-option name="offer_type" ng-repeat="type in offer_type_collection" value="@{{type.id}}">
	      @{{type.title}}
	    </md-option>
	  </md-select>
	  <div ng-messages="frm_offer.offer_type.$error">
		<div ng-message="required">Vous devez renseigner le type</div>
	  </div>
	</md-input-container>				
	<md-input-container class="md-block">
	  <label>Période</label>
	  <md-select ng-model="data.focus.period">
	    <md-option name="offer_period" ng-repeat="period in offer_period_collection" value="@{{period.id}}">
	      @{{period.title}}
	    </md-option>
	  </md-select>
	  <div ng-messages="frm_offer.offer_period.$error">
		<div ng-message="required">Vous devez renseigner la période</div>
	  </div>
	</md-input-container>
			
	<md-input-container class="md-block">
	 	<label>Prix</label>
	 	<input type="text" ng-model="data.focus.price" name="offer_price" required>
	 	<div ng-messages="frm_offer.offer_price.$error">
	 		<div ng-message="required">Vous devez renseigner le prix</div>
	 	</div>
	</md-input-container>
	<md-checkbox ng-model="data.focus.multiple" ng-true-value="1" ng-false-value="0" aria-label="Multiple">
        Autoriser le client à commander plusieurs
    </md-checkbox>		
    <hr/>		
	<h4>Combinaisons</h4>
	<div ng-repeat="pack in data.focus.packs" layout="row" ng-if="!pack.deleted">
		<md-input-container>
			<md-button class="md-icon-button" ng-click="pack.deleted=true" aria-label="@lang("ryshop::overall.removepackcombination")"><md-icon md-font-icon="fa fa-minus-circle"></md-icon></md-button>
		</md-input-container>
		<ng-form name="frm_pack_item" layout="column">
			<div ng-repeat="item in pack.items" layout="row" ng-if="!item.deleted">
				<md-input-container>
					<md-button class="md-icon-button" ng-click="item.deleted=true" aria-label="@lang("ryshop::overall.removepackitem")"><md-icon md-font-icon="fa fa-minus-circle"></md-icon></md-button>
				</md-input-container>
				<md-input-container class="md-block">
				 	<label>Marchandise</label>
				 	<input type="text" ng-model="item.vendible_type" name="offer_pack_item_vendible_type" required>
				 	<div ng-messages="frm_pack_item.offer_pack_item_vendible_type.$error">
				 		<div ng-message="required">Vous devez renseigner la marchandise</div>
				 	</div>
				</md-input-container>				
				<md-input-container class="md-block">
				 	<label>Quantité</label>
				 	<input type="number" ng-model="item.quantity" name="offer_pack_item_quantity">
				 	<div ng-messages="frm_pack_item.offer_pack_item_quantity.$error">
				 		<div ng-message="required">Vous devez renseigner la quantité</div>
				 	</div>
				</md-input-container>
			</div>
			<md-button class="md-raised" ng-click="pack.items.push({})" aria-label="@lang("ryshop::overall.addpackitem")"><md-icon md-font-icon="fa fa-plus-circle"></md-icon> @lang("ryshop::overall.addpackitem")</md-button>
		</ng-form>
		<hr/>
	</div>
	<md-button class="md-raised" ng-click="data.focus.packs.push({items:[{}]})" aria-label="@lang("ryshop::overall.addpackcombination")"><md-icon md-font-icon="fa fa-plus-circle"></md-icon> @lang("ryshop::overall.addpackcombination")</md-button>
	<hr/>
	<md-button type="submit" class="md-raised md-primary" ng-disabled="loading || frm_offer.$pending">Enregistrer <md-icon md-font-icon="fa fa-refresh rotate" ng-show="loading"></md-icon></md-button>
</form>
@stop

@section("formscript")
<script type="text/javascript">
@section("offer.script")
["$scope", "$http", "$window", "$rootScope", function($scope, $http, $window, $rootScope){
	$scope.data = {
		rows : {!!$rows!!},
		focus : {
			packs : [{
				items : [{}]
			}]
		}
	};

	$rootScope.$on('loading:progress', function (){
	    $scope.loading = true;
	});

	$rootScope.$on('loading:finish', function (){
	    $scope.loading = false;
	});

	$scope.offer_period_collection = [
		{id:'', title:'one time'},
	    {id:'annuel', title:'annuel'},
	    {id:'mensuel', title:'mensuel'}
	];

	$scope.offer_type_collection = [
		{id:'once', title:'une fois'},
		{id:'abonnement', title:'abonnement'},
		{id:'forfait', title:'forfait'}
	];

	$scope.reset = function(){
		$scope.data.focus = {packs : [{
			items : [{}]
		}]};
	};

	$scope.submitoffer = function(){
		$http.post("{{action("\Ry\Shop\Http\Controllers\AdminController@postSubmitOffer")}}", $scope.data.focus).then(function(response){
			var edition = false;
			for(var i=0; i<$scope.data.rows.length; i++){
				if(response.data.id && $scope.data.rows[i].id == response.data.id) {
					edition = true;
					$scope.data.rows[i] = response.data;
					break;
				}
			}
			if(response.data.id && !edition) {
				$scope.data.rows.push(response.data);
			}
			$scope.reset();
		});
	};

	$scope.deleteoffer = function(row){
		$http.post("{{action("\Ry\Shop\Http\Controllers\AdminController@postDeleteOffer")}}", row).then(function(){
			$window.location.reload();
		});
	};
}]
@stop
</script>
@stop
