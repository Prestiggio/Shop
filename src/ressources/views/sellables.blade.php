@extends("rymd::layouts.page")

@include("ryshop::admin.offer")

@section("main")
<div layout="row" ng-controller="OfferController">
	<div flex="50" class="md-padding">
		<md-content class="affix">
			@yield("offer.table")
		</md-content>
	</div>
	<div flex="50">
		@yield("offer.form")
	</div>
</div>
@stop

@section("script")
<script type="text/javascript">
(function(angular){

	angular.module("ngApp").controller("OfferController", @yield("offer.script")).controller("UserSectionController", function(){
		
	});
	
})(window.angular);
</script>
@stop