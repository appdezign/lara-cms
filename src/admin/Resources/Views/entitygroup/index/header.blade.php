<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">
			<div class="text-muted p-2">
				{{ $data->objects->total() }} {{ _lanq('lara-admin::'.$entity->getEntityKey().'.entity.entity_plural') }}
			</div>
		</div>
	</div>

	<div class="col-5">
		<div class="search">
			&nbsp;
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			<a href="{{ route('admin.'.$entity->getEntityKey().'.create') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="{{ _lanq('lara-admin::default.button.add') }} {{ _lanq('lara-admin::'.$entity->getEntityKey().'.entity.entity_single') }}">
				<i class="fal fa-plus"></i>
			</a>

			<a href="{{ route('admin.entity.index') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary me-3"
			   title="{{ _lanq('lara-admin::default.button.cancel') }}">
				<i class="fas fa-reply"></i>
			</a>

		</div>
	</div>

</div>

