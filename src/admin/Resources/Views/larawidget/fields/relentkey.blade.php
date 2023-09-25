<?php

use Lara\Common\Models\Entity;

$relatableEntities = Entity::where('entity_key', '!=', 'page')
	->whereHas('objectrelations', function ($query) {
		$query->where('is_relatable', 1);
	})->pluck('entity_key');

$relents = array();
foreach ($relatableEntities as $relatableEntity) {
	$relents[$relatableEntity] = $relatableEntity;
}

?>

@if($data->object->type == 'module')

	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-admin::larawidget.column.relentkey') .':', 'relentkey') }}
		</x-slot>
		{{ html()->select('relentkey', $relents, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
	</x-formrow>

@else

	{{-- Hide disabled fields --}}
	{{ html()->hidden('relentkey', null) }}

@endif