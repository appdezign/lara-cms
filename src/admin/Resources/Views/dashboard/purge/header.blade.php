<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

		</div>
	</div>

	<div class="col-5">
		<div class="search">

		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			@if($data->force == true)
				{{ html()->button('Purge', 'submit', null)->class('btn btn-sm btn-danger swal-proces-purge-confirm') }}
				<a href="{{ route('admin.dashboard.purge', ['force' => 'false']) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-success">
					<i class="las la-unlock"></i>
				</a>
			@else
				{{ html()->button('Purge', 'submit', null)->class('btn btn-sm btn-danger swal-proces-purge-confirm')->disabled() }}
				<a href="{{ route('admin.dashboard.purge', ['force' => 'true']) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-danger">
					<i class="las la-lock"></i>
				</a>
			@endif

		</div>
	</div>
</div>

