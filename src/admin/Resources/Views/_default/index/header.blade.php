<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			@if($entity->hasSearch() && $data->filters->search)
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['reset' => 'true']) }}"
				   class="btn btn-sm btn-outline btn-outline-primary float-end">
					{{ _lanq('lara-admin::default.form.reset_search') }}
				</a>
			@else

				@if($entity->hasFilters())
					@include('lara-admin::_partials.filterbyrelation')
				@elseif($entity->hasTags())
					@include('lara-admin::_partials.filterbytaxonomy')
				@elseif($entity->hasGroups())
					@include('lara-admin::_partials.filterbygroup')
				@else
					<div class="text-muted p-2">
						@if($data->objects instanceof \Illuminate\Pagination\LengthAwarePaginator )
							{{ $data->objects->total() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
						@else
							{{ $data->objects->count() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
						@endif
					</div>
				@endif

			@endif

		</div>
	</div>

	<div class="col-5">
		<div class="search">
			@if($entity->hasSearch() && $data->filters->filter === false)
				@include('lara-admin::_partials.search')
			@else
				&nbsp;
			@endif
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			@if($entity->getEgroup() != 'form')
				@can('create', $entity->getEntityModelClass())
					<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.create') }}"
					   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
					   title="{{ _lanq('lara-admin::default.button.add') }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_single') }}">
						<i class="fal fa-plus"></i>
					</a>
				@endcan
			@endif

			@if($entity->hasFilters())
				<a href="{{ route('admin.'.$entity->getRelationFilterEntitykey().'.index') }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary">
					<i class="fal fa-cube"></i>
				</a>
			@endif

			@if($entity->hasTags())
				<a href="{{ route('admin.tag.index', ['entity' => $entity->getEntityKey()]) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="{{ _lanq('lara-admin::default.button.manage_tags') }}">
					<i class="fal fa-tags"></i>
				</a>
			@endif

			@if($entity->isSortable())
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.reorder') }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="{{ _lanq('lara-admin::default.button.reorder') }}">
					<i class="far fa-arrows"></i>
				</a>
			@endif

			@isset($data->modulepage)
				<a href="{{ route('admin.module.edit', ['id' => $data->modulepage->id]) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="Edit Module Page">
					<i class="fal fa-file-alt"></i>
				</a>
			@endisset

			@if($entity->getEgroup() == 'form')
				@if($data->filters->trashed)
					<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['archive' => 'false']) }}"
					   class="btn btn-sm btn-icon btn-outline btn-outline-primary">
						<i class="fas fa-archive"></i>
					</a>
				@else
					<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['archive' => 'true']) }}"
					   class="btn btn-sm btn-icon btn-outline btn-outline-primary">
						<i class="fal fa-archive"></i>
					</a>
				@endif
			@endif

		</div>
	</div>
</div>

<div class="row index-batch d-none">
	@if($entity->hasBatch())
		@include($data->partials['batch'])
	@endif
</div>

