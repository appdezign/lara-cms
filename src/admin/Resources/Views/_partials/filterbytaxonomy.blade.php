<select class="form-select form-select-sm" data-control="select2" name="tag_id" data-placeholder="Filter" data-hide-search="true" onchange="if (this.value) window.location.href=this.value">

	@if($entity->getMethod() != 'reorder')
		<option value="{!! route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['tag' => 'reset']) !!}">
			@if($data->filters->filterbytaxonomy === true)
				{{ _lanq('lara-admin::default.form.show_all') }}
			@else
				{{ _lanq('lara-admin::default.form.filter') }}
			@endif
		</option>
	@endif

	@foreach($data->tags as $taxonomy => $tags)
		@if(!empty($tags))
			@foreach($tags as $node)
				@include('lara-admin::_partials.filterbytaxonomy_render', $node)
			@endforeach
		@endif
	@endforeach

</select>