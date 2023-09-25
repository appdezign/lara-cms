@if($entity->hasGroups())

	@if($entity->isAlias())

		{{ html()->hidden('cgroup', $entity->getAlias()) }}

	@else

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="minus">
				{{ _lanq('lara-admin::default.boxtitle.group') }}
			</x-boxheader>

			<div class="box-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.group').':', 'cgroup') }}
					</x-slot>
					@if(Auth::user()->isAn('administrator'))
						{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					@else
						{{ html()->hidden('cgroup', 'page') }}
						{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
					@endif
				</x-formrow>

			</div>

		</div>

	@endif

@endif


