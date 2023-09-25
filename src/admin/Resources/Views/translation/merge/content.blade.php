<table class="table table-lara table-row-bordered table-hover">
	<thead>
		<tr>
			<th class="w-5 text-center">
				<div class="form-check">
					{{ html()->checkbox('select_all', false, 1)->id('check-all')->class('js-check-all form-check-input') }}
				</div>
			</th>
			</th>
			<th class="w-75">
				Module
			</th>
			<th class="w-20">
				&nbsp;
			</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data->modules as $mod)
			<tr>
				<td class="text-center">
					<div class="form-check">
						{{ html()->checkbox('objcts[]', false, $mod->key)->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ ucfirst($mod->key) }}
				</td>
			</tr>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				&nbsp;
			</td>
		</tr>
	</tfoot>
</table>

