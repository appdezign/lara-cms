<?php

$templateFileList = \Lara\Common\Models\Templatefile::where('type', $data->object->cgroup)->pluck('template_file',
	'id')->toArray();

$titleTags = [
	'h1' => 'h1',
	'h2' => 'h2',
	'h3' => 'h3',
	'h4' => 'h4',
	'h5' => 'h5',
	'h6' => 'h6',
];

$subTitleTags = [
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
	{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
	{{ html()->hidden('cgroup', null) }}
</x-formrow>

{{-- TEMPLATEWIDGET_ID --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.templatefile_id').':', 'templatefile_id') }}
	</x-slot>
	{{ html()->select('templatefile_id', [null=>'- Select template widget -'] + $templateFileList, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
	{{ html()->hidden('templatefile_id', null) }}
</x-formrow>

{{-- TITLE TAG --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.title_tag').':', 'title_tag') }}
	</x-slot>
	{{ html()->select('title_tag', $titleTags, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

{{-- SUBTITLE TAG --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.subtitle_tag').':', 'subtitle_tag') }}
	</x-slot>
	{{ html()->select('subtitle_tag', $subTitleTags, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

{{-- LIST TAG --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::headertag.column.list_tag').':', 'list_tag') }}
	</x-slot>
	{{ html()->select('list_tag', $listTags, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>