<!--begin::content-header-->
@if(sizeof($data->updates->lara) > 0 || sizeof($data->updates->eve) > 0)
	<div class="row mb-6 fs-5">
		<div class="col-md-6 col-lg-5 offset-lg-1">
			@if(sizeof($data->updates->lara) > 0)
				Lara updates available: <span class="color-primary">{{ sizeof($data->updates->lara) }}</span>
				<i class="far fa-angle-double-right px-2 text-dark"></i>
				<a href="{{ route('admin.dashboard.index', ['update-lara' => 'true']) }}"
				   class="color-danger text-decoration-underline">update now</a>
			@endif
		</div>
		<div class="col-md-6 col-lg-5">
			@if(sizeof($data->updates->eve) > 0)
				App updates available: <span class="color-primary">{{ sizeof($data->updates->eve) }}</span>
				<i class="far fa-angle-double-right px-2 text-dark"></i>
				@if(sizeof($data->updates->lara) > 0)
					<span class="text-muted">update now</span>
				@else
					<a href="{{ route('admin.dashboard.index', ['update-eve' => 'true']) }}"
					   class="color-danger text-decoration-underline">update now</a>
				@endif
			@endif
		</div>
	</div>
@endif

<!--end::content-header-->
