@if($entity->isAlias() && $entity->getAliasIsGroup())

	<h4 class="color-danger"><em>{{ ucfirst($data->filters->cgroup) }}</em></h4>

@else

	<select name="group_id" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter" data-hide-search="true" onchange="if (this.value) window.location.href=this.value">

		@if($entity->getEntityKey() != 'page' && $entity->getMethod() != 'reorder')
			<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['cgroup' => '']) }}">
				@if($data->filters->filter === true)
					{{ _lanq('lara-admin::default.form.show_all') }}
				@else
					{{ _lanq('lara-admin::default.form.filter') }}
				@endif
			</option>
		@endif

		@if($entity->getGroups())
			@foreach($entity->getGroups() as $group)
				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['cgroup' => $group]) }}"
				        @if($group == $data->filters->cgroup) selected @endif >
					{{ $group }}
				</option>
			@endforeach
		@endif
	</select>

@endif
