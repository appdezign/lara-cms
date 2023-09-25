@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="objrel">
		{{ _lanq('lara-admin::entity.boxtitle.settings') }}
	</x-boxheader>

	<div id="kt_card_collapsible_objrel" class="collapse show">
		<div class="box-body">

			{{-- SEO --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has seo:', '_has_seo') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_seo', 0) }}
					{{ html()->checkbox('_has_seo', $data->object->objectrelations->has_seo, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- OPENGRAPH --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has opengraph:', '_has_opengraph') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_opengraph', 0) }}
					{{ html()->checkbox('_has_opengraph', $data->object->objectrelations->has_opengraph, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- LAYOUT --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has layout:', '_has_layout') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_layout', 0) }}
					{{ html()->checkbox('_has_layout', $data->object->objectrelations->has_layout, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- RELATED --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has related:', '_has_related') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_related', 0) }}
					{{ html()->checkbox('_has_related', $data->object->objectrelations->has_related, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- RELATABLE --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('is relatable:', '_is_relatable') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_is_relatable', 0) }}
					{{ html()->checkbox('_is_relatable', $data->object->objectrelations->is_relatable, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			{{-- SYNC --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has sync:', '_has_sync') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_sync', 0) }}
					{{ html()->checkbox('_has_sync', $data->object->objectrelations->has_sync, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>


