<style>
	tr.custom td {
		color: #1969d7;
	}
</style>
<div class="table-responsive">

	@foreach($data->export as $ent)

		<div class="row mt-20 p-6" style="background-color: #f6f8fa;">
			<div class="col-sm-6">
				<h3>{{ $ent->entity_key }}</h3>
			</div>
			<div class="col-sm-6">
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export', $ent->id) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="Copy language content">
					<i class="fal fa-file-export"></i>
				</a>
			</div>
		</div>


		<table class="table table-lara table-row-bordered table-hover mt-6">
			<thead>
				<tr>
					<th class="w-25">
						Field name
					</th>
					<th class="w-25">
						Field type
					</th>
					<th class="w-50">
						Export
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
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>

		</table>
	@endforeach
</div>




