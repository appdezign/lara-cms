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

			@if(config('lara-admin.languages_content.can_export'))
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export') }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="Copy language content">
					<i class="fal fa-clone"></i>
				</a>
			@endif

			@if(config('lara-admin.languages_content.can_purge'))
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.purge') }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="Purge language content">
					<i class="fal fa-trash-alt"></i>
				</a>
			@endif

		</div>

	</div>

</div>






