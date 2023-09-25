<?php

$geotypes = [
	'auto'   => 'Auto',
	'manual' => 'Manual',
	'hide'   => 'Hide',
];

?>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' . $cvar->fieldname).':', $cvar->fieldname) }}
	</x-slot>
	{{ html()->select($cvar->fieldname, $geotypes, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

