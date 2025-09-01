<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			@if($entity->hasSearch() && $data->filters->search)
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['reset' => 'true']) }}"
				   class="btn btn-sm btn-outline btn-outline-primary float-end">
					{{ _lanq('lara-admin::default.form.reset_search') }}
				</a>
			@else
				@if(!$data->showarchive)
					@include('lara-admin::user.index.filterbyrole')
				@endif
			@endif
		</div>
	</div>

	<div class="col-5">
		<div class="search">
			@if($entity->hasSearch())
				@if($data->filters->filter === false)
					@include('lara-admin::_partials.search')
				@else
					&nbsp;
				@endif
			@else
				&nbsp;
			@endif
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

			@if($data->showarchive)
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

		</div>
	</div>

</div>

