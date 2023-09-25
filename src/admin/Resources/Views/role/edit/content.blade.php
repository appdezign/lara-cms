<div class="box box-default">

	<x-boxheader cstate="active" collapseid="content">
		{{ _lanq('lara-admin::default.boxtitle.content') }}
	</x-boxheader>

	<div id="kt_card_collapsible_content" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name').':', 'name') }}
				</x-slot>
				{{ html()->text('name', null)->class('form-control')->required() }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.backend').':', 'has_backend_access') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('has_backend_access', 0) }}
					{{ html()->checkbox('has_backend_access', old('has_backend_access', $data->object->has_backend_access), 1)->class('form-check-input') }}
				</div>

			</x-formrow>

			<div class="my-4">&nbsp;</div>

			<x-formrow>

				<x-slot name="label">
					{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.level').':', 'level') }}
				</x-slot>

				<div class="mb-0">

					<div id="kt_slider_level" data-curval="{{ $data->object->level }}"></div>
					<div class="d-none">
						{{ html()->input('number', 'level', old('level', $data->object->level))->id('kt_slider_level_input')->class('form-control') }}
					</div>

				</div>

			</x-formrow>

		</div>
	</div>

</div>

@foreach($data->entities as $groupkey => $entgroup)

	@if($data->object->has_backend_access == 1 || $groupkey == 'entity')

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="{{ $groupkey }}">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.'.$groupkey) }}
			</x-boxheader>

			<div id="kt_card_collapsible_{{ $groupkey }}" class="collapse show">
				<div class="box-body">

					@foreach($entgroup as $entkey)

						<div class="row form-group">
							<div class="col-2">{{ $entkey }}</div>
							<div class="col-2">
								<div class="form-check">
									{{ html()->checkbox('select_all', in_array($entkey . '_all', $data->objectabilities), 1)->id('check-all')->class($entkey . '_all form-check-input') }}
								</div>
							</div>

							@foreach($data->abilities as $ability)
								@if($entkey == $ability->entity_key)

									<div class="col-1 advanced text-right">
										{{ html()->label($ability->name, $ability->name) }}
									</div>
									<div class="col-1 advanced">
										<div class="form-check">
											{{ html()->checkbox('abilities[]', in_array($ability->entity_key . '_' . $ability->name, $data->objectabilities), $ability->entity_key . '_' . $ability->name)->class($entkey . '_check form-check-input') }}
										</div>
									</div>

								@endif
							@endforeach
						</div>

					@endforeach

				</div>
			</div>

		</div>


	@endif

@endforeach


