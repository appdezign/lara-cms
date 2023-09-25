@if($data->result->error->error)
	<div style="padding:10px; font-size:16px; font-weight: 600; color:#d81b60;">
		Errors: {{ $data->result->error->errorcount }}
	</div>
@else
	<div style="padding:10px; font-size:16px; font-weight: 600; color:#1969d7;">
		Databases are identical
	</div>
@endif

<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th colspan="2" class="text-center">
					<strong>{{ $data->dbsource }}</strong>
				</th>
				<th colspan="2" class="text-center">
					<strong>{{ $data->dbdest }}</strong>
				</th>
			</tr>
			<tr>
				<th class="col-15">
					table
				</th>
				<th class="col-20 text-right">
					column
				</th>
				<th class="col-20">
					column type
				</th>
				<th class="col-20 text-right">
					column
				</th>
				<th class="col-25">
					column type
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach($data->result->objects as $object)

				@foreach($object->columns as $column)
					<tr>
						@if ($loop->first)
							<td>{{ $object->tablename }}</td>
						@else
							<td>&nbsp;</td>
						@endif
						<td class="text-right">{{ $column->columnname }} </td>
						<td>{{ $column->coltypesrc }} @if($column->collengthsrc)
								({{ $column->collengthsrc }})
							@endif</td>

						@if(!$object->tableerror)
							@if(!$column->columnerror)
								<td class="text-right" style="color:#1969d7;">{{ $column->columnname }}</td>
								@if(!$column->typeerror && !$column->lengtherror)
									<td style="color:#1969d7;">
										{{ $column->coltypedest }} @if($column->collengthdest)
											({{ $column->collengthdest }})
										@endif
									</td>
								@else
									@if($column->coltypesrc == 'bigint' && $column->coltypedest == 'integer')
										<td style="color:#d81b60;">
											{{ $column->coltypedest }} @if($column->collengthdest)
												({{ $column->collengthdest }})
											@endif
										</td>
									@else
										<td style="background-color:#d81b60; color:white;">
											{{ $column->coltypedest }} @if($column->collengthdest)
												({{ $column->collengthdest }})
											@endif
											<div class="pull-right">Error: Type mismatch</div>
										</td>
									@endif
								@endif
							@else
								<td colspan="2" class="text-center" style="background-color:#d81b60;color:white;">
									Error: column not found
								</td>
							@endif
						@else
							@if ($loop->first)
								<td colspan="2" class="text-center" style="background-color:#d81b60;color:white;">
									Error: table not found
								</td>
							@else
								<td colspan="2">&nbsp;</td>
							@endif

						@endif
					</tr>
				@endforeach

			@endforeach
		</tbody>
	</table>
</div>