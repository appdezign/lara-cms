@foreach($data->relatables as $relatable)

	<div class="box box-default">

		<x-boxheader cstate="active" collapseid="{{ $relatable['entity_key'] }}">
			{{ $relatable['title'] }}
		</x-boxheader>

		<div id="kt_card_collapsible_{{ $relatable['entity_key'] }}" class="collapse show">
			<div class="box-body">

				@if($relatable['disabled'])

					<div class="row">
						<div class="col">
							<span class="text-muted">{{ _lanq('lara-admin::default.form.related_entity_not_in_menu') }}</span>
						</div>
					</div>

				@else

					<div class="row">
						<div class="col-12 col-sm-8 col-md-6 col-lg-4">

							<table class="table table-lara table-row-bordered table-hover">
								<tbody>
									@foreach($data->related as $rel)
										@if($rel['related_entity_key'] == $relatable['entity_key'])
											<tr>
												<td>
													<span class="color-primary">{{ $rel['title'] }}</span>
												</td>
												<td class="action-icons">
													{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_related')->value('_delete_related_'.$rel['rel_id'])->class('btn btn-link float-end') }}
												</td>
											</tr>
										@endif
									@endforeach
								</tbody>
							</table>

						</div>
					</div>

					<hr>

					@if(sizeof($relatable['objects']) > 0)

						<div class="row p-0">
							<div class="col-3 pb-2">
								{{ _lanq('lara-admin::default.form.add_related') }}:
							</div>
							<div class="col-6 pb-2">
								{{ html()->select('_new_related_'. $relatable['entity_key'], [ null => _lanq('lara-admin::default.form.please_select')] + $relatable['objects'], old('_new_related_'. $relatable['entity_key']))->class('form-select form-select-sm')->data('control', 'select2') }}
							</div>
							<div class="col-1">
								{{ html()->input('submit', '_save_related', 'Go')->class('btn btn-sm btn-outline btn-outline-success float-end') }}
							</div>
						</div>

					@endif

				@endif

			</div>
		</div>
	</div>

@endforeach
