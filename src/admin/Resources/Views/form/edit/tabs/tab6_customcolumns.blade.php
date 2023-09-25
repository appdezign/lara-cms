@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="fieldlist">
		{{ _lanq('lara-admin::entity.boxtitle.fields') }}
	</x-boxheader>

	<div id="kt_card_collapsible_fieldlist" class="collapse show">
		<div class="box-body pt-4 pb-10">

			<div class="row pb-4">
				<div class="col">
					<a href="{{ route('admin.form.reorder', ['id' => $data->object->id]) }}"
					   class="btn btn-sm btn-outline btn-outline-primary float-end"
					   title="{{ _lanq('lara-admin::default.button.reorder') }}">
						<i class="far fa-arrows"></i>
					</a>
				</div>
			</div>

			<div class="row crud-header-row">
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_name') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_friendly_name') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_type') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_hook') }}
				</div>
				<div class="col-1 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_required') }}
				</div>
				<div class="col-1 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_list') }}
				</div>
				<div class="col-1 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_state') }}
				</div>
				<div class="col-1 crud-header-col">
					@if(!$data->entityLocked)
						DELETE
					@else
						&nbsp
					@endif
				</div>

			</div>

			@if(!$data->entityLocked)

				@foreach($data->customcolumns as $field)

					@if($field['field_lock'] == 0)

						<div class="row crud-row edit-crud-row">
							<div class="col-2 crud-col">
								@if(str_contains($field['fieldstate'], 'if'))
									<div class="row">
										<div class="col-3 builder-field-arrow">
											<div class="child-icon">
												<i class="fal fa-chevron-left"></i>
											</div>
										</div>
										<div class="col-9 ps-0">
											{{ html()->text('_mname_'.$field['id'], old('_mname_'.$field['id'], $field['fieldname']))->class('form-control') }}
										</div>
									</div>
								@else
									{{ html()->text('_mname_'.$field['id'], old('_mname_'.$field['id'], $field['fieldname']))->class('form-control') }}
								@endif
							</div>
							<div class="col-2 crud-col">
								{{ html()->text('_mtitle_'.$field['id'], old('_mtitle_'.$field['id'], $field['fieldtitle']))->class('form-control') }}
							</div>
							<div class="col-2 crud-col">
								{{ html()->select('_mtype_'.$field['id'], $data->fieldTypes, old('_mtype_'.$field['id'], $field['fieldtype']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
							</div>
							<div class="col-2 crud-col">
								{{ html()->select('_mhook_'.$field['id'], $data->formFieldHooks, old('_mhook_'.$field['id'], $field['fieldhook']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
							</div>

							<div class="col-1 crud-col">
								<div class="form-check">
									{{ html()->hidden('_mreq_'.$field['id'], 0) }}
									{{ html()->checkbox('_mreq_'.$field['id'], old('_mreq_'.$field['id'], $field['required']), 1)->class('form-check-input') }}
								</div>
							</div>

							<div class="col-1 crud-col">
								<div class="form-check">
									{{ html()->hidden('_mprim_'.$field['id'], 0) }}
									{{ html()->checkbox('_mprim_'.$field['id'], old('_mprim_'.$field['id'], $field['primary']), 1)->class('form-check-input') }}
								</div>
							</div>

							<div class="col-1 crud-col">
								@if($field['fieldstate'] != 'enabled' || $field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
									<button class="btn btn btn-sm btn-success" type="button" data-bs-toggle="collapse"
									        data-bs-target="#state_{{ $field['id'] }}">
										{{ _lanq('lara-admin::entity.button.options') }}
									</button>
								@else
									<button class="btn btn-sm btn-outline btn-outline-success" type="button"
									        data-bs-toggle="collapse"
									        data-bs-target="#state_{{ $field['id'] }}">
										{{ _lanq('lara-admin::entity.button.options') }}
									</button>
								@endif
							</div>
							<div class="col-1 crud-col">
								{{ html()->text('_mdelete_'.$field['id'], old('_mdelete_'.$field['id']))->class('form-control') }}
							</div>

						</div>

						<div class="row crud-row editcrud-row">
							<div class="col">

								<div class="collapse" id="state_{{ $field['id'] }}">
									<div class="builder-field-details mt-3">
										<div class="row">
											<div class="col-2 p-2">
												{{ html()->label(_lanq('lara-admin::entity.column.state'), '_mstate_'.$field['id']) }}
											</div>
											<div class="col-2">
												{{ html()->select('_mstate_'.$field['id'], $data->fieldStates, old('_mstate_'.$field['id'], $field['fieldstate']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
											</div>
											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->select('_mcondfield_'.$field['id'], $data->fieldList, old('_mcondfield_'.$field['id'], $field['condition_field']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
												@else
													{{ html()->hidden('_mcondfield_'.$field['id'], null) }}
												@endif
											</div>
											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->select('_mcondop_'.$field['id'], ['isequal' => 'is equal to', 'isnotequal' => 'is not equal to'], old('_mcondop_'.$field['id'], $field['condition_operator']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
												@else
													{{ html()->hidden('_mcondop_'.$field['id'], null) }}
												@endif
											</div>

											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->text('_mcondval_'.$field['id'], old('_mcondval_'.$field['id'], $field['condition_value']))->class('form-control')->style(['font-style' => 'italic']) }}
												@else
													{{ html()->hidden('_mcondval_'.$field['id'], null) }}
												@endif
											</div>
										</div>

										@if($field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
											<div class="row mt-5">
												<div class="col-2 p-2">
													{{ html()->label('Select one of:', '_mdata_'.$field['id']) }}
												</div>
												<div class="col-10">
													{{ html()->textarea('_mdata_'.$field['id'], old('_mdata_'.$field['id'], $field['fielddata']))->class('form-control')->rows(3) }}
												</div>
											</div>
										@else
											{{ html()->hidden('_mdata_'.$field['id'], '') }}
										@endif

									</div>
								</div>
							</div>
						</div>

					@else

						{{--  Disable all inputs, because field is locked --}}

						<div class="row crud-row editcrud-row">
							<div class="col-2 crud-col">
								@if(str_contains($field['fieldstate'], 'if'))
									<div class="row">
										<div class="col-3 builder-field-arrow">
											<div class="child-icon">
												<i class="fal fa-chevron-left"></i>
											</div>
										</div>
										<div class="col-9 ps-0">
											{{ html()->hidden('_mname_'.$field['id'], $field['fieldname']) }}
											{{ html()->text('_mname_'.$field['id'], old('_mname_'.$field['id'], $field['fieldname']))->class('form-control')->disabled() }}
										</div>
									</div>
								@else
									{{ html()->hidden('_mname_'.$field['id'], $field['fieldname']) }}
									{{ html()->text('_mname_'.$field['id'], old('_mname_'.$field['id'], $field['fieldname']))->class('form-control')->disabled() }}
								@endif
							</div>
							<div class="col-2 crud-col">
								{{ html()->hidden('_mtitle_'.$field['id'], $field['fieldtitle']) }}
								{{ html()->text('_mtitle_'.$field['id'], old('_mtitle_'.$field['id'], $field['fieldtitle']))->class('form-control')->disabled() }}
							</div>
							<div class="col-2 crud-col">
								{{ html()->hidden('_mtype_'.$field['id'], $field['fieldtype']) }}
								{{ html()->select('_mtype_'.$field['id'], $data->fieldTypes, old('_mtype_'.$field['id'], $field['fieldtype']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
							</div>
							<div class="col-2 crud-col">
								{{ html()->hidden('_mhook_'.$field['id'], $field['fieldtype']) }}
								{{ html()->select('_mhook_'.$field['id'], $data->formFieldHooks, old('_mhook_'.$field['id'], $field['fieldhook']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
							</div>
							<div class="col-1 crud-col">
								{{ html()->hidden('_mreq_'.$field['id'], $field['required']) }}
								{{ $field['required'] }}
							</div>
							<div class="col-1 crud-col">
								{{ html()->hidden('_mprim_'.$field['id'], $field['primary']) }}
								{{ $field['primary'] }}
							</div>
							<div class="col-1 crud-col">
								@if($field['fieldstate'] != 'enabled' || $field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
									<button class="btn btn btn-sm btn-success" type="button" data-bs-toggle="collapse"
									        data-bs-target="#state_{{ $field['id'] }}">
										{{ _lanq('lara-admin::entity.button.options') }}
									</button>
								@else
									<button class="btn btn btn-sm btn-outline btn-outline-success" type="button"
									        data-bs-toggle="collapse"
									        data-bs-target="#state_{{ $field['id'] }}">
										{{ _lanq('lara-admin::entity.button.options') }}
									</button>
								@endif
							</div>
							<div class="col-1 crud-col text-center">
								<i class="las la-lock text-muted fs-4"></i>
							</div>

						</div>

						<div class="row crud-row editcrud-row">
							<div class="col">

								<div class="collapse" id="state_{{ $field['id'] }}">
									<div class="builder-field-details mt-3">
										<div class="row">
											<div class="col-2 p-2">
												{{ html()->label(_lanq('lara-admin::entity.column.state'), '_mstate_'.$field['id']) }}
											</div>
											<div class="col-2">
												{{ html()->hidden('_mstate_'.$field['id'], $field['fieldstate']) }}
												{{ html()->select('_mstate_'.$field['id'], $data->fieldStates, old('_mstate_'.$field['id'], $field['fieldstate']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
											</div>
											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->hidden('_mcondfield_'.$field['id'], $field['condition_field']) }}
													{{ html()->select('_mcondfield_'.$field['id'], $data->fieldList, old('_mcondfield_'.$field['id'], $field['condition_field']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
												@else
													{{ html()->hidden('_mcondfield_'.$field['id'], null) }}
												@endif
											</div>

											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->hidden('_mcondop_'.$field['id'], $field['condition_operator']) }}
													{{ html()->select('_mcondop_'.$field['id'], ['isequal' => 'is equal to', 'isnotequal' => 'is not equal to'], old('_mcondop_'.$field['id'], $field['condition_operator']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
												@else
													{{ html()->hidden('_mcondop_'.$field['id'], null) }}
												@endif
											</div>

											<div class="col-2">
												@if($field['fieldstate'] == 'enabledif')
													{{ html()->hidden('_mcondval_'.$field['id'], $field['condition_value']) }}
													{{ html()->text('_mcondval_'.$field['id'], old('_mcondval_'.$field['id'], $field['condition_value']))->class('form-control')->style(['font-style' => 'italic'])->disabled() }}
												@else
													{{ html()->hidden('_mcondval_'.$field['id'], null) }}
												@endif
											</div>
										</div>

										@if($field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
											<div class="row mt-5">
												<div class="col-2 p-2">
													{{ html()->label('Select one of:', '_mdata_'.$field['id']) }}
												</div>
												<div class="col-10">
													{{ html()->hidden('_mdata_'.$field['id'], $field['fielddata']) }}
													{{ html()->textarea('_mdata_'.$field['id'], old('_mdata_'.$field['id'], $field['fielddata']))->class('form-control')->rows(3)->disabled() }}
												</div>
											</div>
										@else
											{{ html()->hidden('_mdata_'.$field['id'], '') }}
										@endif

									</div>
								</div>
							</div>
						</div>

					@endif

				@endforeach

			@else

				@foreach($data->customcolumns as $field)
					<div class="row crud-row">
						<div class="col-2 crud-col">
							@if(str_contains($field['fieldstate'], 'if'))
								<div class="row">
									<div class="col-3 builder-field-arrow">
										<div class="child-icon">
											<i class="fal fa-chevron-left"></i>
										</div>
									</div>
									<div class="col-9 ps-0">
										{{ $field['fieldname'] }}
									</div>
								</div>
							@else
								{{ $field['fieldname'] }}
							@endif
						</div>
						<div class="col-2 crud-col">
							{{ $field['fieldtitle'] }}
						</div>
						<div class="col-2 crud-col">
							{{ $field['fieldtype'] }}
						</div>
						<div class="col-2 crud-col">
							{{ $field['fieldhook'] }}
						</div>

						<div class="col-1 crud-col">
							{{ $field['required'] }}
						</div>
						<div class="col-1 crud-col">
							{{ $field['primary'] }}
						</div>
						<div class="col-1 crud-col">
							@if($field['fieldstate'] != 'enabled' || $field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
								<button class="btn btn-sm btn-outline btn-outline-success" type="button"
								        data-bs-toggle="collapse"
								        data-bs-target="#state_{{ $field['id'] }}">
									{{ _lanq('lara-admin::entity.button.options') }}
								</button>
							@endif
						</div>
						<div class="col-1 crud-col">&nbsp;</div>
					</div>
					<div class="row crud-row editcrud-row">
						<div class="col">

							<div class="collapse" id="state_{{ $field['id'] }}">
								<div class="builder-field-details mt-3">
									<div class="row">
										<div class="col-2">
											{{ _lanq('lara-admin::entity.column.state') }}
										</div>
										<div class="col-2">
											{{ $field['fieldstate'] }}
										</div>
										<div class="col-2">
											{{ $field['condition_field'] }}
										</div>
										<div class="col-2">
											{{ $field['condition_operator'] }}
										</div>
										<div class="col-2">
											{{ $field['condition_value'] }}
										</div>
									</div>

									@if($field['fieldtype'] == 'selectone' || $field['fieldtype'] == 'selectone2one' || $field['fieldtype'] == 'radio')
										<div class="row mt-5">
											<div class="col-2">
												{{ _lanq('lara-admin::entity.general.select_one_of') }}:
											</div>
											<div class="col-10">
												{{ $field['fielddata'] }}
											</div>
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				@endforeach

			@endif

		</div>
	</div>

</div>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="fieldadd">
		{{ _lanq('lara-admin::entity.boxtitle.add_field') }}
	</x-boxheader>

	<div id="kt_card_collapsible_fieldadd" class="collapse show">
		<div class="box-body py-10">

			<div class="row crud-header-row">
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_name') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_friendly_name') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.field_type') }}
				</div>
				<div class="col-2 crud-header-col">
					&nbsp;
				</div>
				<div class="col-2 crud-header-col">&nbsp;</div>
				<div class="col-2 crud-header-col">&nbsp;</div>
			</div>

			@if(!$data->entityLocked)

				<div class="row crud-row form-group">
					<div class="col-2 crud-col">
						{{ html()->text('_new_fieldname', null)->class('form-control') }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->text('_new_fieldtitle', null)->class('form-control') }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->select('_new_fieldtype', $data->fieldTypes, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->hidden('_new_fieldhook', 'default') }}
					</div>
					<div class="col-4 crud-col">
						{{ html()->input('submit', 'cust_col_add', _lanq('lara-admin::default.button.add'))->class('btn btn-sm btn-danger') }}
					</div>

				</div>

			@else

				<div class="row crud-row form-group">
					<div class="col-2 crud-col">
						{{ html()->text('_new_fieldname', null)->class('form-control')->disabled() }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->text('_new_fieldtitle', null)->class('form-control')->disabled() }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->select('_new_fieldtype', $data->fieldTypes, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>
					<div class="col-2 crud-col">
						{{ html()->hidden('_new_fieldhook', 'default') }}
					</div>
					<div class="col-4 crud-col">
						{{ html()->input('submit', 'cust_col_add', _lanq('lara-admin::default.button.add'))->class('btn btn-sm btn-danger')->disabled() }}
					</div>
				</div>

			@endif

		</div>
	</div>

</div>
