@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="info">
		{{ _lanq('lara-admin::entity.boxtitle.info') }}
	</x-boxheader>

	<div id="kt_card_collapsible_info" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.model_class').':', 'entity_model_class') }}
				</x-slot>
				{{ html()->text('entity_model_class', null)->class('form-control')->disabled() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.controller').':', 'entity_controller') }}
				</x-slot>
				{{ html()->text('entity_controller', null)->class('form-control')->disabled() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.entity_key').':', 'entity_key') }}
				</x-slot>
				{{ html()->text('entity_key', null)->class('form-control')->disabled() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.title').':', 'title') }}
				</x-slot>
				{{ html()->text('title', null)->class('form-control')->required() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.group').':', 'group_id') }}
				</x-slot>
				{{ html()->select('group_id', $data->entityGroups, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.menu_parent').':', 'menu_parent') }}
				</x-slot>
				{{ html()->select('menu_parent', $data->menuParents, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.menu_position').':', 'menu_position') }}
				</x-slot>
				{{ html()->text('menu_position', null)->class('form-control') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.menu_icon').':', 'menu_icon') }}
				</x-slot>
				@if($data->object->getMenuParent() == 'root')
					{{ html()->text('menu_icon', null)->class('form-control') }}
				@else
					{{ html()->text('menu_icon', null)->class('form-control')->disabled() }}
				@endif
			</x-formrow>

		</div>
	</div>

</div>


<div class="box box-default">

	<x-boxheader cstate="active" collapseid="routes">
		{{ _lanq('lara-admin::entity.boxtitle.routes') }}
	</x-boxheader>

	<div id="kt_card_collapsible_routes" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::entity.column.resource_routes').':', 'resource_routes') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('resource_routes', 0) }}
					{{ html()->checkbox('resource_routes', null, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label('front auth:', 'has_front_auth') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('has_front_auth', 0) }}
					{{ html()->checkbox('has_front_auth', $data->object->has_front_auth, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>


@if($data->object->egroup->key == 'entity')
	<div class="box box-default collapsed-box">

		<x-boxheader cstate="collapsed" collapseid="advanced">
			{{ _lanq('lara-admin::entity.boxtitle.advanced') }}
		</x-boxheader>

		<div id="kt_card_collapsible_advanced" class="collapse">
			<div class="box-body">

				<a href="{{ route('admin.'.$entity->entity_key.'.destroy', ['id' => $data->object->id ]) }}"
				   data-token="{{ csrf_token() }}"
				   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}"
				   data-method="delete">
					<i class="las la-trash"></i> {{ _lanq('lara-admin::entity.button.delete_entity') }}
				</a>

			</div>
		</div>

	</div>

@endif