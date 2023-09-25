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
	{{ html()->text('key', null)->class('form-control')->required() }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.path').':', 'path') }}
	</x-slot>
	<div class="select-two-md">
	{{ html()->select('path', ['Lara' => 'Lara', 'Eve' => 'Eve'], null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</div>
</x-formrow>