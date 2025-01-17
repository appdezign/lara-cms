<?php
$cols = config('lara-admin.userProfile');
$cvars = json_decode(json_encode($cols), false);
?>

@foreach($cvars as $cvar)

		<?php
		$cvarfname = $cvar->name;
		$cvarfieldname = '_profile_' . $cvarfname;
		$cvarvalue = $data->object->profile->$cvarfname;
		?>

	@if($cvar->readonly)

		<x-showrow>
			<x-slot name="label">
				{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' .$cvarfname) .':', $cvarfieldname) }}
			</x-slot>
			{{ $cvarvalue }}
		</x-showrow>

	@else

		<x-formrow>

			<x-slot name="label">
				{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' .$cvarfname) .':', $cvarfieldname) }}
			</x-slot>

			@if($cvar->type == 'varchar')
				{{ html()->text($cvarfieldname, $cvarvalue)->class('form-control') }}
			@endif

			@if($cvar->type == 'text')
				{{ html()->textarea($cvarfieldname, $cvarvalue)->class('form-control')->rows(4) }}
			@endif

			@if($cvar->type == 'int')
				{{ html()->input('number', $cvarfieldname, $cvarvalue)->class('form-control')->attributes(['step' => '1']) }}
			@endif

			@if($cvar->type == 'tinyint')
				<div class="form-check">
					{{ html()->hidden($cvarfieldname, 0) }}
					{{ html()->checkbox($cvarfieldname, $cvarvalue, 1)->class('form-check-input') }}
				</div>
			@endif

		</x-formrow>
	@endif

@endforeach

