@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="sortable">
		{{ _lanq('lara-admin::entity.boxtitle.sortable') }}
	</x-boxheader>

	<div id="kt_card_collapsible_sortable" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.sort_manual').':', '_is_sortable') }}
				</x-slot>

				<div class="form-check">
					@if($data->object->egroup->key == 'entity')
						{{ html()->hidden('_is_sortable', 0) }}
						{{ html()->checkbox('_is_sortable', $data->object->columns->is_sortable, 1)->id('entity_is_sortable')->class('form-check-input') }}
					@else
						{{ html()->hidden('_is_sortable', $data->object->columns->is_sortable) }}
						{{ html()->checkbox('_is_sortable', $data->object->columns->is_sortable, 1)->id('entity_is_sortable')->class('form-check-input sortableCheckbox')->disabled() }}
					@endif
				</div>

			</x-formrow>

			<div class="sortable-options">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::entity.column.sort_field').':', '_sort_field') }}
					</x-slot>
					{{ html()->select('_sort_field', $data->sortfields, $data->object->columns->sort_field)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::entity.column.sort_order').':', '_sort_order') }}
					</x-slot>
					{{ html()->select('_sort_order', ['asc' => 'asc', 'desc' => 'desc'], $data->object->columns->sort_order)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::entity.column.sort2_field').':', '_sort2_field') }}
					</x-slot>
					{{ html()->select('_sort2_field', [null=>'- None -'] + $data->sortfields, $data->object->columns->sort2_field)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::entity.column.sort2_order').':', '_sort2_order') }}
					</x-slot>
					{{ html()->select('_sort2_order', [null=>'- None -'] + ['asc' => 'asc', 'desc' => 'desc'], $data->object->columns->sort2_order)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</x-formrow>

			</div>

		</div>
	</div>

</div>
