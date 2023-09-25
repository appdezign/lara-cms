<div class="row page-title module-page-title">

	<!--begin:Page Title-->
	<div class="col-12 col-md-6">
		<h1 class="page-heading text-dark fw-light fs-1">Refresh Google Analytics</h1>
	</div>

	<div class="col-12 col-md-6 text-md-end">
		<div class="page-heading text-dark">
			GA4 property: {{ config('analytics.property_id') }}
			@if(Auth::user()->isAn('administrator') && is_numeric(config('analytics.account_id')))
				<a href="https://analytics.google.com/analytics/web/#/a{{ config('analytics.account_id') }}p{{ config('analytics.property_id') }}/admin"
				   target="_blank">
					<i class="far fa-external-link color-danger"></i>
				</a>
			@endif
		</div>
	</div>
	<!--end:Page Title-->

</div>


