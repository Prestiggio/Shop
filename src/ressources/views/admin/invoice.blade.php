@extends("rymd::layouts.page")

@section("main")
<div layout="row" ng-controller="InvoiceController">
	<div flex="50" class="md-padding">
		<md-content class="affix">
			<table style="width:100%" class="table table-bordered">
			  <tr>    
	    			<th>réference</th>       
					<th>client</th>
	    			<th>total payé TTC</th>    
	    			<th>total TTC</th>
			  </tr>
			  <tbody ng-repeat="row in data.rows">
			 	<tr> 	
	    			<td>@{{row.order.reference}}</td>
					<td>@{{row.order.cart.customer.facturable.name}}</td>  	
	    			<td>@{{row.total_paid_tax_incl}}</td>
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
				<h4>Paiements</h4>
				<ng-form name="frm_payment" ng-repeat="payment in data.focus.payments" ng-if="!payment.deleted">
					<md-input-container class="md-block">
					 	<label>Montant</label>
					 	<input type="text" ng-model="payment.amount" name="payment_amount" required>
					 	<div ng-messages="frm_payment.payment_amount.$error">
					 		<div ng-message="required">Vous devez renseigner le montant du paiement</div>
					 	</div>
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Méthode de paiement</label>
					 	<input type="text" ng-model="payment.payment_method" name="payment_payment_method" required>
					 	<div ng-messages="frm_payment.payment_payment_method.$error">
					 		<div ng-message="required">Vous devez renseigner la méthode de paiement</div>
					 	</div>
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Code de la transaction</label>
					 	<input type="text" ng-model="payment.transaction_id" name="payment_transaction_id">
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Numéro de la carte (si carte)</label>
					 	<input type="text" ng-model="payment.card_number" name="payment_card_number">
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Marque de la carte (si carte)</label>
					 	<input type="text" ng-model="payment.card_brand" name="payment_card_brand">
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Expiration de la carte (si carte)</label>
					 	<input type="text" ng-model="payment.card_expiration" name="payment_card_expiration">
					</md-input-container>
					<md-input-container class="md-block">
					 	<label>Propriétaire de la carte (si carte)</label>
					 	<input type="text" ng-model="payment.card_holder" name="payment_card_holder">
					</md-input-container>
					<md-button class="md-accent md-raised" ng-click="cancelPayment($index)">Annuler le paiement</md-button>
				</ng-form>
				<md-button class="md-primary md-raised" ng-click="addPayment()">Nouveau paiement</md-button>
				<md-button type="submit" class="md-raised md-primary" ng-disabled="loading || frm_invoice.$pending">Enregistrer</md-button>
			</form>
	</div>
</div>
@stop

@section("script")
<script type="text/javascript">
(function(angular, $, undefined){

	Date.prototype.toYMD = Date_toYMD;
    function Date_toYMD() {
        var year, month, day, hours, minutes, seconds;
        year = String(this.getFullYear());
        month = String(this.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(this.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        hours = String(this.getHours());
        if (hours.length == 1) {
        	hours = "0" + hours;
        }
        minutes = String(this.getMinutes());
        if (minutes.length == 1) {
        	minutes = "0" + minutes;
        }
        seconds = String(this.getSeconds());
        if (seconds.length == 1) {
        	seconds = "0" + seconds;
        }
        return year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds;
    };
    function String2Date() {
    	var s = this.match(/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/);
    	var d = new Date();
    	d.setFullYear(s[1]);
    	d.setMonth(parseInt(s[2])+1);
    	d.setDate(parseInt(s[3]));
    	d.setHours(parseInt(s[4]));
    	d.setMinutes(parseInt(s[5]));
    	d.setSeconds(parseInt(s[6]));
    	return d;
    };
    String.prototype.toDate = String2Date;
	
	angular.module("ngApp").controller("InvoiceController", ["$scope", "$http", "$window", function ($scope, $http, $window) {
		$scope.data = {
				rows : {!!$rows!!},
				focus : {
					payments : []
				}
			};

		$scope.loading = false;

			for(var d in $scope.data.rows) {
				$scope.data.rows[d].delivery_date = $scope.data.rows[d].delivery_date.toDate();
			}

			$scope.reset = function(){
				$scope.data.focus = {};
			};

			$scope.submitinvoice = function(){
				$scope.loading = true;
				$http.post("{{action("\Ry\Shop\Http\Controllers\AdminController@postInvoice")}}", $scope.data.focus).then(function(){
					$window.location.reload();
				});
			};

			$scope.deleteinvoice = function(row){
				$http.delete("{{action("\Ry\Shop\Http\Controllers\AdminController@deleteInvoice")}}", {params:{id:row.id}}).then(function(){
					$window.back();
				});
			}

			$scope.addPayment = function(){
				$scope.data.focus.payments.push({});
			};

			$scope.cancelPayment = function($index){
				if($scope.data.focus.payments[$index].id)
					$scope.data.focus.payments[$index].deleted = true;
				else
					$scope.data.focus.payments.splice($index, 1);
			};
			
		}]).controller("UserSectionController", function(){
		
		});
	
})(window.angular, window.jQuery);
</script>
@stop