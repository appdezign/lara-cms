<div class="box box-default">

	<x-boxheader cstate="active" collapseid="content">
		{{ _lanq('lara-admin::default.boxtitle.content') }}
	</x-boxheader>

	<div id="kt_card_collapsible_content" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.username').':', 'username') }}
				</x-slot>
				{{ html()->text('username', null)->class('form-control')->disabled()->required() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.email').':', 'email') }}
				</x-slot>
				{{ html()->text('email', null)->class('form-control')->required() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.firstname').':', 'firstname') }}
				</x-slot>
				{{ html()->text('firstname', null)->class('form-control') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.middlename').':', 'middlename') }}
				</x-slot>
				{{ html()->text('middlename', null)->class('form-control') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.lastname').':', 'lastname') }}
				</x-slot>
				{{ html()->text('lastname', null)->class('form-control') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name').':', 'name') }}
				</x-slot>
				{{ html()->text('name', null)->class('form-control')->required() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.new_password').':', '_password') }}
				</x-slot>
				{{ html()->password('_password')->class('form-control')->attributes(['autocomplete' => 'new-password']) }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.language').':', 'user_language') }}
				</x-slot>
				<div class="select-two-md">
					{{ html()->select('user_language', $data->backendLanguages, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>

<div class="box box-default" style="display: none">

	<x-boxheader cstate="active" collapseid="fields">
		{{ _lanq('lara-admin::default.boxtitle.settings') }}
	</x-boxheader>

	<div id="kt_card_collapsible_fields" class="collapse show">
		<div class="box-body">

			@include('lara-admin::user.fields.fields')

		</div>
	</div>

</div>
