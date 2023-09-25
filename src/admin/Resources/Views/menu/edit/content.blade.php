<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->required() }}
</x-formrow>

@include('lara-admin::_partials.slug')

