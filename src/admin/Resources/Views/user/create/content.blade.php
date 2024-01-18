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
				{{ html()->text('username', null)->class('form-control')->required() }}
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
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.password').':', 'password') }}
				</x-slot>
				{{ html()->password('password')->class('form-control')->attributes(['autocomplete' => 'new-password']) }}
			</x-formrow>

			{{ html()->hidden('email_verified_at', Date::parse(\Carbon\Carbon::now())->format('Y-m-d H:i:s')) }}

		</div>
	</div>

</div>


<div class="box box-default">

	<x-boxheader cstate="active" collapseid="roles">
		{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.roles') }}
	</x-boxheader>

	<div id="kt_card_collapsible_roles" class="collapse show">
		<div class="box-body">

			@foreach($data->roles as $role)

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(ucfirst($role->name), $role->name) }}
					</x-slot>
					<div class="form-check">
						@if($loop->last)
							{{ html()->radio('role_name', true, $role->name)->id('role_name_'.$role->id)->class('form-check-input') }}
						@else
							{{ html()->radio('role_name', false, $role->name)->id('role_name_'.$role->id)->class('form-check-input') }}
						@endif
					</div>

				</x-formrow>

			@endforeach

		</div>
	</div>

</div>







