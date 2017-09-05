<form name="frm_havecode" class="md-padding" novalidate ng-submit="frm_havecode.$valid && checkcode()">
	<md-dialog-content>
		<div class="md-headline">@lang("ryshop::overall.entercode")</div>
		<md-input-container>
			<label>@lang("ryshop::overall.entercode")</label>
			<input type="text" name="code" ng-model="user.order_reference" required>
			<div ng-messages="frm_havecode.code.$error">
	         	<div ng-message="required">Vous devez renseigner un code valide</div>
	        </div>
	        <div ng-show="error" style="text-color:#dd2c00;">@{{error}}</div>
		</md-input-container>
	</md-dialog-content>
	<md-dialog-actions>
		<md-button type="submit" class="md-raised md-primary">@lang("rymd::overall.validate")</md-button>
	</md-dialog-actions>
</form>