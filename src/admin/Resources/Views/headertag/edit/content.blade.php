<?php
$entityList = \Lara\Common\Models\Entity::EntityGroupIs('entity')->pluck('entity_key', 'id')->toArray();
$laraWidgetList = \Lara\Common\Models\Larawidget::where('type', 'module')->pluck('title', 'id')->toArray();
$templateWidgetList = \Lara\Common\Models\Templatewidget::pluck('templatewidget', 'id')->toArray();

$titleTags = [
	'h1' => 'h1',
	'h2' => 'h2',
	'h3' => 'h3',
	'h4' => 'h4',
	'h5' => 'h5',
	'h6' => 'h6',
];

$listTags = [
	'h2' => 'h2',
	'h3' => 'h3',
	'h4' => 'h4',
	'h5' => 'h5',
	'h6' => 'h6',
];

?>

{{-- TITLE --}}
<x-formrowreq emessage="{{ _lanq('lara-admin::default.message.error_required_field') }}">
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->attribute('required') }}
</x-formrowreq>

{{-- CGROUP --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.cgroup').':', 'cgroup') }}
	</x-slot>
	{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

{{-- ENTITY_ID --}}
@if($data->object->cgroup == 'module')
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-admin::headertag.column.entity_id').':', 'entity_id') }}
		</x-slot>
		{{ html()->select('entity_id', [null=>'- Select module -'] + $entityList, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
	</x-formrow>
@else
	{{ html()->hidden('entity_id', null) }}
@endif

{{-- LARAWIDGET_ID --}}
@if($data->object->cgroup == 'larawidget')
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-admin::headertag.column.larawidget_id').':', 'larawidget_id') }}
		</x-slot>
		{{ html()->select('larawidget_id', [null=>'- Select widget -'] + $laraWidgetList, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
	</x-formrow>
@else
	{{ html()->hidden('larawidget_id', null) }}
@endif

{{-- TEMPLATEWIDGET_ID --}}
@if($data->object->cgroup == 'templatewidget')
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-admin::headertag.column.templatewidget_id').':', 'templatewidget_id') }}
		</x-slot>
		{{ html()->select('templatewidget_id', [null=>'- Select template widget -'] + $templateWidgetList, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
	</x-formrow>
@else
	{{ html()->hidden('templatewidget_id', null) }}
@endif

{{-- TITLE TAG --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.title_tag').':', 'title_tag') }}
	</x-slot>
	{{ html()->select('title_tag', $titleTags, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

{{-- LIST TAG --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.list_tag').':', 'list_tag') }}
	</x-slot>
	{{ html()->select('list_tag', $listTags, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>