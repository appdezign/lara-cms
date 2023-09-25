<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			<div class="text-muted p-2">
				@if($data->objects instanceof \Illuminate\Pagination\LengthAwarePaginator )
					{{ $data->objects->total() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
				@else
					{{ $data->objects->count() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
				@endif
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

			@can('create', $entity->getEntityModelClass())
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.create') }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="{{ _lanq('lara-admin::default.button.add') }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_single') }}">
					<i class="fal fa-plus"></i>
				</a>
			@endcan

		</div>
	</div>

</div>

