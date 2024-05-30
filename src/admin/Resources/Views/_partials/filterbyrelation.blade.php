<?php

$filtermodelclass = $entity->getRelationFilterModelclass();

$filterobjects = $filtermodelclass::langIs($clanguage)->pluck('title', 'id');

?>

<select name="tag" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter" data-hide-search="true" onchange="if (this.value) window.location.href=this.value">

	<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['relfilter' => '']) }}">
		@if($data->filters->filter === true)
			{{ _lanq('lara-admin::default.form.show_all') }}
		@else
			{{ _lanq('lara-admin::default.form.filter') }}
		@endif
	</option>

	@foreach($filterobjects as $filtobjectid => $filtobjecttitle) {
	<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['relfilter' => $filtobjectid]) }}"
	        @if($filtobjectid == $data->filters->filterrelation) selected @endif >
		{{ $filtobjecttitle }}
	</option>
	@endforeach

</select>

