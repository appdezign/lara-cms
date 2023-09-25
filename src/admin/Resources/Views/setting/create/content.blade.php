<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->required() }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.group').':', 'value') }}
	</x-slot>
	{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}

</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.key').':', 'key') }}
	</x-slot>
	{{ html()->text('key', null)->class('form-control') }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value').':', 'value') }}
	</x-slot>
	{{ html()->text('value', null)->class('form-control') }}
</x-formrow>

{{ html()->hidden('locked_by_admin', 1) }}




