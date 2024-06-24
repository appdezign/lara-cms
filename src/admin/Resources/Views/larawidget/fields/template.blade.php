<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->GetEntityKey().'.column.template') .':', 'template') }}
	</x-slot>
	{{ html()->select('template', $data->widgetTemplates, null)->class('form-select form-select-sm')
		->data('control', 'select2')->data('hide-search', 'true')
		->if($cvardisabled, function ($el) {
			return $el->disabled();
		}) }}
</x-formrow>