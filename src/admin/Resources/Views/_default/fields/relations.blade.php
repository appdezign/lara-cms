@foreach($entity->getRelations() as $relation)

	@php
		$relatedModelClass = $relation->relatedEntity->getEntityModelClass();
		$relatedSortField = $relation->relatedEntity->columns->sort_field;
		$relatedSortOrder = $relation->relatedEntity->columns->sort_order;
		$relatedObjects = $relatedModelClass::langIs($clanguage)->orderBy($relatedSortField, $relatedSortOrder)->pluck('title', 'id');
	@endphp

	@if($relation->type == 'belongsTo')

		<x-formrow>
			<x-slot name="label">
				{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' . $relation->foreign_key).':', $relation->foreign_key) }}
			</x-slot>
			{{ html()->select($relation->foreign_key, $relatedObjects, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
		</x-formrow>

	@endif

@endforeach
