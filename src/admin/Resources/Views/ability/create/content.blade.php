<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name').':', 'name') }}
	</x-slot>
	{{ html()->select('name', $data->crudlist, old('name'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.entity_type').':', 'entity_type') }}
	</x-slot>
	{{ html()->select('entity_type', $data->entitytypes, old('entity_type'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>









