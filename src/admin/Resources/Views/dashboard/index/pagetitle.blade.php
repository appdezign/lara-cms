<div class="row page-title module-page-title">

	<!--begin:Page Title-->
	<div class="col-6 col-sm-3 order-1 order-sm-1 d-flex align-content-center">
		<h1 class="page-heading d-flex text-dark fw-light fs-1 flex-column justify-content-center my-0">{{ title_case(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_title')) }}</h1>
	</div>
	<!--end:Page Title-->

	<!--begin:Message-->
	<div class="col-12 col-sm-5 order-3 order-sm-2">
		&nbsp;
	</div>
	<!--end:Message-->

	<!--begin:Tools-->
	<div class="col-6 col-sm-4 order-2 order-sm-3">

		<span class="text-muted">
			{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.last_sync') }} : {{ Carbon\Carbon::parse($data->lastsync)->format('Y-m-d H:i') }}
		</span>

		<a href="{{ route('admin.dashboard.refresh') }}" class="btn btn-icon-danger">
			<i class="far fa-sync-alt"></i>
		</a>
	</div>
	<!--end:Tools-->

</div>


