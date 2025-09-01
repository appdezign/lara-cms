<style>
	tr.custom td {
		color: #1969d7;
	}
</style>
<div class="table-responsive">

	@foreach($data->export as $ent)

		<div class="row mt-20 pt-4 pb-4" style="background-color: #e6eef6;">
			<div class="col-sm-6">
				<div class="ps-4">
					<h3>{{ $ent->entity_key }}</h3>
				</div>
			</div>
			<div class="col-sm-6">
				@if($ent->columns)
					<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export', $ent->id) }}"
					   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
					   title="Copy language content">
						<i class="fal fa-file-export"></i>
					</a>
				@endif
			</div>
		</div>


		@if($ent->columns)
			<table class="table table-lara table-row-bordered dmt-6">
				<thead>
					<tr>
						<th class="w-20">
							Field name
						</th>
						<th class="w-20">
							Field type
						</th>
						<th class="w-20">
							Export
						</th>
						<th class="w-20">
							Content
						</th>
						<th class="w-20">
							Action
						</th>
					</tr>
				</thead>
				<tbody>

					@foreach($ent->columns as $key => $col)
						<tr class="{{ $col['group'] }}">
							<td>{{ $col['fieldname'] }}</td>
							<td>{{ $col['fieldtype'] }}</td>
							<td>
								@if($col['export'])
									<i class="far fa-check-square fs-2 color-danger"></i>
								@else
									@if($col['group'] == 'standard')
										N/A
									@else
										<i class="fal fa-square fs-2 color-grey"></i>
									@endif
								@endif
							</td>
							<td>
								@if($col['totalrows'])
									{{ $col['contentrows'] }} / {{ $col['totalrows'] }}
								@endif
							</td>
							<td>
								@if(!$col['export'] && $col['contentrows'] && $col['contentrows'] > 0)
									<i class="fas fa-2x fa-info-circle color-danger"></i>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>

			</table>
		@endif
	@endforeach
</div>




