<x-formrowbadge
		labelfield="entity_key"
		labeltext="{{ _lanq('lara-admin::entity.column.entity_key') }}"
		badgeplacement="top"
		badgetitle="{{ _lanq('lara-admin::entity.infobadge.create_entity_key_title') }}"
		badgecontent="{{ _lanq('lara-admin::entity.infobadge.create_entity_key_body') }}">
	{{ html()->text('entity_key', null)->class('form-control')->required() }}
</x-formrowbadge>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::entity.column.group').':', 'group_id') }}
	</x-slot>
	{{ html()->select('group_id', $data->entityGroups, $data->defaultGroup->id)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

<x-formrow>
	<x-slot name="label">
		{{ html()->label(_lanq('lara-admin::entity.column.menu_parent').':', 'menu_parent') }}
	</x-slot>
	{{ html()->select('menu_parent', $data->menuParents, 'modules')->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
</x-formrow>

