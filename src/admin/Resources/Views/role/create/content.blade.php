<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name').':', 'name') }}
	</x-slot>
	{{ html()->text('name', null)->class('form-control')->required() }}
</x-formrow>

<div class="my-4">&nbsp;</div>

<x-formrow>

	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.level').':', 'level') }}
	</x-slot>

	<div class="mb-0">

		<div id="kt_slider_level" data-curval="10"></div>
		<div class="d-none">
			{{ html()->input('number', 'level', old('level'))->id('kt_slider_level_input')->class('form-control') }}
		</div>

	</div>

</x-formrow>






