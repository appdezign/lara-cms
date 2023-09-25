<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->disabled() }}
	{{ html()->hidden('title', $data->object->title) }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.group').':', 'value') }}
	</x-slot>
	{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
	{{ html()->hidden('cgroup', $data->object->cgroup) }}

</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.key').':', 'key') }}
	</x-slot>
	{{ html()->text('key', null)->class('form-control')->disabled() }}
	{{ html()->hidden('key', $data->object->key) }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value').':', 'value') }}
	</x-slot>
	@if(!empty(config('lara-admin.settings.' . $data->object->key)))

		{{ html()->select('value', config('lara-admin.settings.' . $data->object->key) , null)->class('form-select form-select-sm')
			->data('control', 'select2')->data('hide-search', 'true') }}
	@else
		{{ html()->text('value', null)->class('form-control') }}
	@endif

</x-formrow>

@if(Auth::user()->isAn('administrator'))
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.locked_by_admin').':', 'locked_by_admin') }}
		</x-slot>
		<div class="form-check">
			{{ html()->hidden('locked_by_admin', 0) }}
			{{ html()->checkbox('locked_by_admin', null, 1)->class('form-check-input') }}
		</div>
	</x-formrow>
@else
	{{ html()->hidden('locked_by_admin', 0) }}
@endif

