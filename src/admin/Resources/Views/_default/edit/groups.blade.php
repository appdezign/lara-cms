@if($entity->hasGroups())

	@if($entity->isAlias())

		{{ html()->hidden('cgroup', $entity->getAlias()) }}

	@else

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="group">
				{{ _lanq('lara-admin::default.boxtitle.group') }}
			</x-boxheader>

			<div id="kt_card_collapsible_group" class="collapse show">
				<div class="box-body">

					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-admin::default.column.cgroup').':', 'cgroup') }}
						</x-slot>
						{{ html()->select('cgroup', $entity->getGroups(), null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</x-formrow>

				</div>
			</div>
		</div>


	@endif

@endif


