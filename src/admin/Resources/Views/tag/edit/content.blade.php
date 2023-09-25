<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->required() }}
</x-formrow>

@include('lara-admin::_partials.slug')

{{-- LEAD --}}
@if($entity->hasLead())
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.lead').':', 'lead') }}
		</x-slot>
		@if($entity->hasTinyLead())
			{{ html()->textarea('lead', null)->class('form-control tinymin')->rows(4) }}
		@else
			{{ html()->textarea('lead', null)->class('form-control')->rows(4) }}
		@endif
	</x-formrow>
@endif

{{-- BODY --}}
@if($entity->hasBody())
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.body').':', 'body') }}
		</x-slot>
		@if($entity->hasTinyBody())
			{{ html()->textarea('body', null)->class('form-control tiny') }}
		@else
			{{ html()->textarea('body', null)->class('form-control') }}
		@endif
	</x-formrow>
@endif

@if(Auth::user()->isAn('administrator'))
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.locked_by_admin').':', 'locked_by_admin') }}
		</x-slot>
		{{ html()->hidden('locked_by_admin', 0) }}
		{{ html()->checkbox('locked_by_admin', null, 1)->class('form-check-input') }}
</x-formrow>
@else
{{ html()->hidden('locked_by_admin', null) }}
@endif

