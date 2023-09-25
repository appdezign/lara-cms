@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="options">
		{{ _lanq('lara-admin::entity.boxtitle.options') }}
	</x-boxheader>

	<div id="kt_card_collapsible_options" class="collapse show">
		<div class="box-body">

			{{-- USER --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has user:', '_has_user') }}
				</x-slot>
				<div class="form-check">
					@if($data->object->egroup->key == 'entity')
						@if($data->totalCount == 0)
							{{ html()->hidden('_has_user', 0) }}
							{{ html()->checkbox('_has_user', $data->object->columns->has_user, 1)->class('form-check-input') }}
						@else
							{{ html()->hidden('_has_user', $data->object->columns->has_user) }}
							{{ html()->checkbox('_has_user', $data->object->columns->has_user, 1)->class('form-check-input')->disabled() }}
						@endif
					@else
						{{ html()->hidden('_has_user', $data->object->columns->has_user) }}
						{{ html()->checkbox('_has_user', $data->object->columns->has_user, 1)->class('form-check-input')->disabled() }}
					@endif
				</div>
			</x-formrow>

			{{-- LANG --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has lang:', '_has_lang') }}
				</x-slot>
				<div class="form-check">
					@if($data->object->egroup->key == 'entity')
						{{ html()->hidden('_has_lang', 0) }}
						{{ html()->checkbox('_has_lang', $data->object->columns->has_lang, 1)->class('form-check-input') }}
					@else
						{{ html()->hidden('_has_lang', $data->object->columns->has_lang) }}
						{{ html()->checkbox('_has_lang', $data->object->columns->has_lang, 1)->class('form-check-input')->disabled() }}
					@endif
				</div>
			</x-formrow>

			{{-- SLUG --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has slug:', '_has_slug') }}
				</x-slot>
				<div class="form-check">

					@if($data->object->egroup->key == 'entity')
						{{ html()->hidden('_has_slug', 0) }}
						{{ html()->checkbox('_has_slug', $data->object->columns->has_slug, 1)->class('form-check-input') }}
					@else
						{{ html()->hidden('_has_slug', $data->object->columns->has_slug) }}
						{{ html()->checkbox('_has_slug', $data->object->columns->has_slug, 1)->class('form-check-input')->disabled() }}
					@endif
				</div>
			</x-formrow>

			{{-- LEAD --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has lead:', '_has_lead') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_lead', 0) }}
					{{ html()->checkbox('_has_lead', $data->object->columns->has_lead, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- BODY --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has body:', '_has_body') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_body', 0) }}
					{{ html()->checkbox('_has_body', $data->object->columns->has_body, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- STATUSBOX --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has status:', '_has_status') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_status', 0) }}
					{{ html()->checkbox('_has_status', $data->object->columns->has_status, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- HIDE IN LIST --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has hideinlist:', '_has_hideinlist') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_hideinlist', 0) }}
					{{ html()->checkbox('_has_hideinlist', $data->object->columns->has_hideinlist, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- EXPIRATION --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has expiration:', '_has_expiration') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_expiration', 0) }}
					{{ html()->checkbox('_has_expiration', $data->object->columns->has_expiration, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- APP --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has app:', '_has_app') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_app', 0) }}
					{{ html()->checkbox('_has_app', $data->object->columns->has_app, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- FIELDS --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has fields:', '_has_fields') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_fields', 0) }}
					{{ html()->checkbox('_has_fields', $data->object->columns->has_fields, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>






