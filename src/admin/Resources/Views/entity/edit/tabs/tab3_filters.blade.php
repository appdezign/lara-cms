@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="groups">
		{{ _lanq('lara-admin::entity.boxtitle.groups') }}
	</x-boxheader>

	<div id="kt_card_collapsible_groups" class="collapse show">
		<div class="box-body">

			{{-- USE EITHER TAGS, GROUPS OR FILTERS --}}

			{{-- GROUPS --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has groups:', '_has_groups') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_groups', 0) }}
					{{ html()->checkbox('_has_groups', $data->object->columns->has_groups, 1)->id('checkbox_has_groups')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="groupValues">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Group values:', '_group_values') }}
					</x-slot>
					{{ html()->text('_group_values', $data->object->columns->group_values)->class('form-control') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Group default:', '_group_default') }}
					</x-slot>
					{{ html()->text('_group_default', $data->object->columns->group_default)->class('form-control') }}
				</x-formrow>

			</div>

			{{-- TAGS --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has tags:', '_has_tags') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_tags', 0) }}
					{{ html()->checkbox('_has_tags', $data->object->objectrelations->has_tags, 1)->id('checkbox_has_tags')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="tagValues">
				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Tag default:', '_tag_default') }}
					</x-slot>
					{{ html()->text('_tag_default', $data->object->objectrelations->tag_default)->class('form-control') }}
				</x-formrow>
			</div>

			{{-- deprecated --}}
			{{ html()->hidden('_has_filters', 0) }}


		</div>
	</div>

</div>




