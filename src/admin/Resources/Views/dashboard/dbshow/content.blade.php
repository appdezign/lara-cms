<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">

		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>
					<strong>{{ $data->dbcurrent }}</strong>
				</th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<th class="col-30">
					table
				</th>
				<th class="col-30">
					column
				</th>
				<th class="col-40">
					column type
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach($data->objects as $object)
				@foreach($object->columns as $column)
					<tr>
						@if ($loop->first)
							<td>{{ $object->tablename }}</td>
						@else
							<td>&nbsp;</td>
						@endif
						<td>{{ $column->columnname }}</td>
						<td>{{ $column->columntype }} @if($column->columnlength)
								({{ $column->columnlength }})
							@endif</td>
					</tr>
				@endforeach

			@endforeach
		</tbody>
	</table>
</div>