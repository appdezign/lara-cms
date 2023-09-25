@include('lara-admin::_partials.count')

<div class="box box-default">
	<x-boxheader cstate="active" collapseid="relationlist">
		{{ _lanq('lara-admin::entity.boxtitle.relations') }}
	</x-boxheader>

	<div id="kt_card_collapsible_relationlist" class="collapse show">
		<div class="box-body pb-10">

			<div class="row crud-header-row">
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.source_entity') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.relation_type') }}
				</div>
				<div class="col-3 crud-header-col">
					{{ _lanq('lara-admin::entity.column.related_entity') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.relation_foreign_key') }}
				</div>
				<div class="col-1 crud-header-col">
					{{ _lanq('lara-admin::entity.column.relation_is_filter') }}
				</div>
				<div class="col-2 crud-header-col">
					@if(!$data->entityLocked)
						DELETE
					@else
						&nbsp
					@endif
				</div>
			</div>

			@if(!$data->entityLocked)

					<?php $filterfound = false ?>

				@foreach($data->relations as $relation)

					<div class="row crud-row">
						<div class="col-2 crud-col">{{ $data->object->entity_key }}</div>
						<div class="col-2 crud-col">{{ $relation->type }}</div>
						<div class="col-3 crud-col">{{ $relation->relatedEntity->entity_key }}</div>
						<div class="col-2 crud-col">{{ $relation->foreign_key }}</div>
						<div class="col-1 crud-col">

							@if($relation->type == 'belongsTo')

								@if($filterfound)
									{{ html()->hidden('_mfilt_'.$relation->id, 0) }}
									{{ html()->checkbox('_mfilt_'.$relation->id, old('_mfilt_'.$relation->id, $relation->is_filter), 1)->class('form-check-input')->disabled() }}
								@else
									{{ html()->hidden('_mfilt_'.$relation->id, 0) }}
									{{ html()->checkbox('_mfilt_'.$relation->id, old('_mfilt_'.$relation->id, $relation->is_filter), 1)->class('form-check-input') }}
								@endif

							@else

								{{ html()->hidden('_mfilt_'.$relation->id, 0) }}

							@endif

						</div>
						<div class="col-2 crud-col">

							{{ html()->text('_rdelete_'.$relation['id'], null)->class('form-control') }}

						</div>
					</div>

						<?php
						if ($relation->is_filter == 1) {
							$filterfound = true;
						}
						?>

				@endforeach

			@else

				@foreach($data->relations as $relation)
					<div class="row crud-row">
						<div class="col-2 crud-col">{{ $data->object->entity_key }}</div>
						<div class="col-2 crud-col">{{ $relation->type }}</div>
						<div class="col-3 crud-col">{{ $relation->relatedEntity->entity_key }}</div>
						<div class="col-2 crud-col">{{ $relation->foreign_key }}</div>
						<div class="col-1 crud-col">
							@if($relation->type == 'belongsTo')
								{{ $relation->is_filter }}
							@endif
						</div>
						<div class="col-2 crud-col">&nbsp;</div>
					</div>
				@endforeach

			@endif

		</div>
	</div>

</div>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="relationadd">
		{{ _lanq('lara-admin::entity.boxtitle.add_relationship') }}
	</x-boxheader>

	<div id="kt_card_collapsible_relationadd" class="collapse show">
		<div class="box-body pb-10">

			<div class="row crud-header-row">
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.source_entity') }}

				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.relation_type') }}

					<x-badge placement="top">
						<x-slot name="title">
							{{ _lanq('lara-admin::entity.infobadge.relation_type_title') }}
						</x-slot>
						{{ _lanq('lara-admin::entity.infobadge.relation_type_body') }}
					</x-badge>

				</div>
				<div class="col-3 crud-header-col">
					{{ _lanq('lara-admin::entity.column.related_entity') }}

					<x-badge placement="top">
						<x-slot name="title">
							{{ _lanq('lara-admin::entity.infobadge.related_entity_title') }}
						</x-slot>
						{{ _lanq('lara-admin::entity.infobadge.related_entity_body') }}
					</x-badge>

				</div>
				<div class="col-3 crud-header-col">
					&nbsp;
				</div>

				<div class="col-2 crud-header-col">&nbsp;</div>
			</div>

			@if(!$data->entityLocked)

				<div class="row crud-row form-group">
					<div class="col-2 crud-col">
						{{ $data->object->entity_key }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->select('_new_relation_type', [null=>'- Select -'] + $data->relationTypes, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>
					<div class="col-3 crud-col">
						{{ html()->select('_new_relation_relid', [null=>'- Select -'] + $data->relatableEntities, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>
					<div class="col-3 crud-col">
						{{ html()->text('_new_relation_foreignkey', null)->class('form-control')->placeholder('foreign key (optional)') }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->input('submit', 'relation_add', _lanq('lara-admin::default.button.add'))->class('btn btn-sm btn-danger') }}
					</div>
				</div>

			@else

				<div class="row crud-row form-group">
					<div class="col-2 crud-col">
						{{ $data->object->entity_key }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->select('_new_relation_type', [null=>'- Select -'] + $data->relationTypes, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
					</div>
					<div class="col-3 crud-col">
						{{ html()->select('_new_relation_relid', [null=>'- Select -'] + $data->relatableEntities, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
					</div>
					<div class="col-3 crud-col">
						{{ html()->text('_new_relation_foreignkey', null)->class('form-control')->placeholder('foreign key (optional)')->disabled() }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->input('submit', 'relation_add', _lanq('lara-admin::default.button.add'))->class('btn btn-sm btn-danger')->disabled() }}
					</div>
				</div>
			@endif

		</div>
	</div>

</div>
