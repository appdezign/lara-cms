@if(empty($data->objects))

	{{ _lanq('lara-admin::default.message.default_language') }}

@else

	<div class="table-responsive">
		<table class="table table-lara table-row-bordered table-striped table-hover">
			<thead>
				<tr>
					<th class="w-5">
						ID
					</th>
					<th class="w-40">
						title
					</th>
					<th class="w-5">
						ID
					</th>
					<th class="w-40">
						title
					</th>
					<th class="w-10">
						edit
					</th>
				</tr>
			</thead>
			<tbody>

				@foreach($data->objects as $ent => $entobjects)

					<tr>
						<td colspan="5"><h3>{{ $ent }}</h3></td>
					</tr>

					<tr>
						<td colspan="2">
							<h4 class="fw-bold color-danger">{{ $data->baseLangCode }}</h4>
						</td>
						<td colspan="2">
							<h4 class="fw-bold color-danger">{{ $data->relLangCode }}</h4>
						</td>
					</tr>
					<tr>
						<td colspan="5"></td>
					</tr>

					@foreach($entobjects as $obj)

						<tr>
							<td class="text-muted">{{ $obj->base_id }}</td>
							<td>{{ str_limit($obj->base_title, 50) }}</td>
							<td class="text-muted">{{ $obj->rel_id }}</td>
							<td>{{ str_limit($obj->rel_title, 50) }}</td>
							<td>
								<a href="javascript:void(0)" onclick="showLangRow({{ $obj->base_id }})">edit</a>
							</td>
						</tr>
						<tr id="row_{{ $obj->base_id }}" class="langrow" style="display:none;">
							<td colspan="3"></td>
							<td colspan="2">
								<div class="row mt-8">
									<div class="col-12 pe-10">
										{{ html()->select($ent . '_' . $obj->base_id, [0 => '- none -'] + $data->relobjects[$ent], $obj->rel_id)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}

									</div>
								</div>

								<div class="row mt-8 mb-8">
									<div class="col">
										{{ html()->input('submit', 'saverel', 'save_' . $ent . '_' . $obj->base_id)->class('btn btn-danger')->style('width: 80px; height: 40px; text-indent:-9999px; background-image: url(/assets/admin/img/pink-save-button.png); background-repeat: no-repeat; background-position: 50% 50%; background-size: cover; ') }}
									</div>
								</div>
							</td>

						</tr>
					@endforeach

					<tr>
						<td colspan="5">&nbsp;</td>
					</tr>

				@endforeach

			</tbody>
			<tfoot>
				<tr>
					<td colspan="7">&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</div>

@endif


