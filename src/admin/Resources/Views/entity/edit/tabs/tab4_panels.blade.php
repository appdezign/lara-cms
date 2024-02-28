@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="panels">
		{{ _lanq('lara-admin::entity.boxtitle.panels') }}
	</x-boxheader>

	<div id="kt_card_collapsible_panels" class="collapse show">
		<div class="box-body">

			{{-- SEARCH --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has search:', '_has_search') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_search', 0) }}
					{{ html()->checkbox('_has_search', $data->object->panels->has_search, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- BATCH --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has batch:', '_has_batch') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_batch', 0) }}
					{{ html()->checkbox('_has_batch', $data->object->panels->has_batch, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- FILTERS --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has filters:', '_has_filters') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_filters', 0) }}
					{{ html()->checkbox('_has_filters', $data->object->panels->has_filters, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- SHOW AUTHOR --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('show author:', '_show_author') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_show_author', 0) }}
					{{ html()->checkbox('_show_author', $data->object->panels->show_author, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- SHOW STATUS --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('show status:', '_show_status') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_show_status', 0) }}
					{{ html()->checkbox('_show_status', $data->object->panels->show_status, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- TINY LEAD --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has tiny (lead):', '_has_tiny_lead') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_tiny_lead', 0) }}
					{{ html()->checkbox('_has_tiny_lead', $data->object->panels->has_tiny_lead, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- TINY BODY --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has tiny (body):', '_has_tiny_body') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_tiny_body', 0) }}
					{{ html()->checkbox('_has_tiny_body', $data->object->panels->has_tiny_body, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>




