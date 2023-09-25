<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->required() }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::'.$entity->getEntityKey().'.column.key').':', 'key') }}
	</x-slot>
	{{ html()->text('key', null)->class('form-control')->disabled() }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.path').':', 'path') }}
	</x-slot>
	<div class="select-two-md">
		{{ html()->select('path', ['Lara' => 'Lara', 'Eve' => 'Eve'], null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
	</div>
</x-formrow>


{{-- OBJECT RELATIONS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has object relations:', 'group_has_objectrelations') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_objectrelations', 0) }}
		{{ html()->checkbox('group_has_objectrelations', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- COLUMNS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has columns:', 'group_has_columns') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_columns', 0) }}
		{{ html()->checkbox('group_has_columns', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- FIELDS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has custom columns:', 'group_has_customcolumns') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_customcolumns', 0) }}
		{{ html()->checkbox('group_has_customcolumns', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- VIEWS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has views:', 'group_has_views') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_views', 0) }}
		{{ html()->checkbox('group_has_views', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- WIDGETS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has widgets:', 'group_has_widgets') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_widgets', 0) }}
		{{ html()->checkbox('group_has_widgets', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- RELATIONS --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has relations:', 'group_has_relations') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_relations', 0) }}
		{{ html()->checkbox('group_has_relations', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- SORTABLE --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has sortable:', 'group_has_sortable') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_sortable', 0) }}
		{{ html()->checkbox('group_has_sortable', 1, null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- MEDIA --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has media:', 'group_has_media') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_media', 0) }}
		{{ html()->checkbox('group_has_media', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>

{{-- MANAGED TABLE --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label('has managed table:', 'group_has_managedtable') }}
	</x-slot>
	<div class="form-check">
		{{ html()->hidden('group_has_managedtable', 0) }}
		{{ html()->checkbox('group_has_managedtable', null, 1)->class('form-check-input') }}
	</div>
</x-formrow>




