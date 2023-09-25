<?php

$autoFilterColumn = 'type';

$modelClass = $entity->getEntityModelClass();
$widgetTypes = $modelClass::distinct($autoFilterColumn)->pluck($autoFilterColumn);

?>

<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			<select name="tag" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter"
			        data-hide-search="true"
			        onchange="if (this.value) window.location.href=this.value">

				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), [$autoFilterColumn => '']) }}">
					@if($data->filters->autofilter === true)
						{{ _lanq('lara-admin::default.form.show_all') }}
					@else
						{{ _lanq('lara-admin::default.form.filter') }}
					@endif
				</option>

				@foreach($widgetTypes as $widgetType)
					<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), [$autoFilterColumn => $widgetType]) }}"
					        @if(array_key_exists($autoFilterColumn, $data->filters->autofilters) && $widgetType == $data->filters->autofilters[$autoFilterColumn]) selected @endif >
						{{ $widgetType }}
					</option>
				@endforeach
			</select>

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

