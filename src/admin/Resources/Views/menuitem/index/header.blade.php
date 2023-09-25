<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			@include('lara-admin::menuitem.index.filterbygroup')

		</div>
	</div>

	<div class="col-5">
		<div class="search">
			&nbsp;
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			<a class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   data-bs-toggle="modal" data-bs-target="#menuCreateModal">
				<i class="fal fa-plus"></i>
			</a>

			<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.reorder') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="{{ _lanq('lara-admin::default.button.reorder') }}">
				<i class="far fa-arrows"></i>
			</a>

			<a href="{{ route('admin.menu.index') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="{{ _lanq('lara-admin::default.button.manage_tags') }}">
				<i class="fal fa-clone"></i>
			</a>

		</div>
	</div>

</div>

