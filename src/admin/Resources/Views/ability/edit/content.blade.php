<div class="box box-default">

	<x-boxheader cstate="active" collapseid="content">
		{{ _lanq('lara-admin::default.boxtitle.content') }}
	</x-boxheader>

	<div id="kt_card_collapsible_content" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
				</x-slot>
				{{ html()->text('title', null)->class('form-control')->disabled() }}
				{{ html()->hidden('title', old('title', $data->object->title)) }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name').':', 'name') }}
				</x-slot>
				{{ html()->select('name', $data->crudlist, old('name', $data->object->name))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
				{{ html()->hidden('name', old('name', $data->object->name)) }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.entity_type').':', 'entity_type') }}
				</x-slot>
				{{ html()->select('entity_type', $data->entitytypes, old('entity_type', $data->object->entity_type))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
				{{ html()->hidden('entity_type', old('entity_type', $data->object->entity_type)) }}
			</x-formrow>

		</div>
	</div>


</div>








