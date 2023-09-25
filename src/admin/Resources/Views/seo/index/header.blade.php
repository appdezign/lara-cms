<div class="row index-toolbar">

	<div class="col-3">
		<div class="filters">
			&nbsp;
		</div>
	</div>

	<div class="col-5">
		<div class="search">
			&nbsp;
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			@if(Auth::user()->isAn('administrator'))
				@if($data->force)

					{{ html()->input('submit', 'batchreset', 'Reset')->class('btn btn-sm btn-danger swal-seo-reset-confirm') }}

					<a href="{{ route('admin.'.$entity->getEntityKey().'.index', ['force' => 'false']) }}"
					   class="btn btn-sm btn-icon btn-success">
						<i class="las la-unlock"></i>
					</a>

				@else
					<a href="{{ route('admin.'.$entity->getEntityKey().'.index', ['force' => 'true']) }}"
					   class="btn btn-sm btn-icon btn-danger">
						<i class="las la-lock"></i>
					</a>
				@endif
			@endif

		</div>
	</div>

</div>

