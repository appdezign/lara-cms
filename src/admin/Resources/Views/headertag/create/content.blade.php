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

{{ html()->hidden('entity_id', null) }}
{{ html()->hidden('larawidget_id', null) }}
{{ html()->hidden('templatewidget', null) }}

{{ html()->hidden('title_tag', 'h2') }}
{{ html()->hidden('list_tag', 'h3') }}
