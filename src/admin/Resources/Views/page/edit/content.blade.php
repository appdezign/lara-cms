{{-- HOOKS BEFORE --}}
@if($entity->hasFields())
	@include($data->partials['fields'], ['fhook' => 'before'])
@endif

{{-- TITLE --}}
<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
	</x-slot>
	{{ html()->text('title', null)->class('form-control')->required() }}
</x-formrow>

{{-- SLUG --}}
@include('lara-admin::_partials.slug')

{{-- RELATIONS --}}
@include($data->partials['relations'])

{{-- HOOKS BETWEEN --}}
@if($entity->hasFields())
	@include($data->partials['fields'], ['fhook' => 'between'])
@endif

{{-- LEAD --}}
@if($entity->hasLead() && in_array($data->object->cgroup, config('lara-admin.page.cgroups_with_lead')))
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
			@if($data->object->cgroup == 'email')
				{{ html()->textarea('body', null)->class('form-control tinymin') }}
			@else
				{{ html()->textarea('body', null)->class('form-control tiny') }}
			@endif
		@else
			{{ html()->textarea('body', null)->class('form-control') }}
		@endif
	</x-formrow>
@endif

{{-- HOOKS AFTER --}}
@if($entity->hasFields())
	@include($data->partials['fields'], ['fhook' => 'after'])
@endif

{{-- HOOKS DEFAULT --}}
@if($entity->hasFields())
	@include($data->partials['fields'], ['fhook' => 'default'])
@endif
