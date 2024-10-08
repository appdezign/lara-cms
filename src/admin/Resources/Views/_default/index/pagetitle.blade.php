<div class="row page-title module-page-title mt-4">

	<!--begin:Page Title-->
	<div class="col-6 col-sm-3 order-1 order-sm-1">
		<h1 class="page-heading text-dark fw-light fs-1 my-0">
			{{ title_case(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_title')) }}
			@if(isset($data->filters) && $data->filters->trashed)
				&dash; <span class="spam-tag">Spam</span>
			@endif
		</h1>
	</div>
	<!--end:Page Title-->

	<!--begin:Message-->
	<div class="col-12 col-sm-6 order-3 order-sm-2">
		@include('flash::message')
	</div>
	<!--end:Message-->

	<!--begin:Tools-->
	<div class="col-6 col-sm-3 order-2 order-sm-3">
		@include('lara-admin::_partials.filterbyuser')
	</div>
	<!--end:Tools-->

</div>


