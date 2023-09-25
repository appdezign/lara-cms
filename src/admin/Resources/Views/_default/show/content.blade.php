@foreach($entity->getCustomColumns() as $cvar)

	@if($cvar->fieldhook == 'before')

		@php
			$colname = $cvar->fieldname;
		@endphp

		<x-showrow>
			<x-slot name="label">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' . $colname) }}
			</x-slot>
			@if($cvar->fieldtype == 'boolean')
				@if($data->object->$colname == 1)
					{{ _lanq('lara-admin::default.value.yes') }}
				@else
					{{ _lanq('lara-admin::default.value.no') }}
				@endif
			@else
				{!! $data->object->$colname !!}
			@endif
		</x-showrow>

	@endif

@endforeach

<x-showrow>
	<x-slot name="label">
		{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
	</x-slot>
	<h1>{!! $data->object->title !!}</h1>
</x-showrow>

<x-showrow>
	<x-slot name="label">
		{{ _lanq('lara-admin::default.column.slug') }}
	</x-slot>
	{!! $data->object->slug !!}
</x-showrow>

@foreach($entity->getCustomColumns() as $cvar)

	@if($cvar->fieldhook == 'between')

		@php
			$colname = $cvar->fieldname;
		@endphp

		<x-showrow>
			<x-slot name="label">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' . $colname) }}
			</x-slot>
			@if($cvar->fieldtype == 'boolean')
				@if($data->object->$colname == 1)
					{{ _lanq('lara-admin::default.value.yes') }}
				@else
					{{ _lanq('lara-admin::default.value.no') }}
				@endif
			@else
				{!! $data->object->$colname !!}
			@endif
		</x-showrow>

	@endif

@endforeach

@if($entity->hasLead())

	<x-showrow>
		<x-slot name="label">
			{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.lead') }}
		</x-slot>
		{!! $data->object->lead !!}
	</x-showrow>

@endif

@if($entity->hasBody())

	<x-showrow>
		<x-slot name="label">
			{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.body') }}
		</x-slot>
		{!! $data->object->body !!}
	</x-showrow>

@endif

@foreach($entity->getCustomColumns() as $cvar)

	@if($cvar->fieldhook == 'after')

		@php
			$colname = $cvar->fieldname;
		@endphp

		<x-showrow>
			<x-slot name="label">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' .$colname) }}
			</x-slot>
			@if($cvar->fieldtype == 'boolean')
				@if($data->object->$colname == 1)
					{{ _lanq('lara-admin::default.value.yes') }}
				@else
					{{ _lanq('lara-admin::default.value.no') }}
				@endif
			@else
				{!! $data->object->$colname !!}
			@endif
		</x-showrow>

	@endif

@endforeach

@foreach($entity->getCustomColumns() as $cvar)

	@if($cvar->fieldhook == 'default')

		@php
			$colname = $cvar->fieldname;
		@endphp

		<x-showrow>
			<x-slot name="label">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' .$colname) }}
			</x-slot>
			@if($cvar->fieldtype == 'boolean')
				@if($data->object->$colname == 1)
					{{ _lanq('lara-admin::default.value.yes') }}
				@else
					{{ _lanq('lara-admin::default.value.no') }}
				@endif
			@else
				{!! $data->object->$colname !!}
			@endif
		</x-showrow>

	@endif

@endforeach


