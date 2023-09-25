<div class="row">
	<div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">

		<table class="table table-lara-sortable">
			<tbody class="sortable" data-entityname="builder">
				@foreach($data->customcolumns as $field)
					<tr class="sortable-row" data-itemId="{{ $field->id }}">
						<td class="sortable-handle"><span class="glyphicon glyphicon-sort"></span></td>
						<td class="sortable-handle">{{ $field->fieldname }}</td>
						<td class="sortable-handle">{{ $field->fieldtype }}</td>
						<td class="sortable-handle">{{ $field->fieldhook }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

	</div>
</div>

