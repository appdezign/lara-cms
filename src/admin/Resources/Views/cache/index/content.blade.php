<table class="table table-lara table-row-bordered table-hover">
	<thead>
		<tr>
			<th class="w-5 text-center">
				<div class="form-check form-check-batch">
					{{ html()->checkbox('select_all', true, 1)->id('check-all')->class('js-check-all  form-check-input') }}
				</div>
			</th>
			</th>
			<th class="w-75">
				{{ _lanq('lara-admin::cache.column.type') }}
			</th>
			<th class="w-20">
				&nbsp;
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', true, 'appcache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				Application Cache
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', true, 'configcache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				Config Cache
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', true, 'viewcache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				View Cache
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', true, 'httpcache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				HttpCache
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', true, 'routecache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				Routes Cache
			</td>
		</tr>
	</tbody>
</table>

<table class="table table-lara table-row-bordered table-hover">
	<thead>
		<tr>
			<th class="w-5">
				&nbsp;
			</th>
			</th>
			<th class="w-75">
				{{ _lanq('lara-admin::cache.column.type') }}
			</th>
			<th class="w-20">
				&nbsp;
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', false, 'anacache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				Refresh Analytics
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<div class="form-check">
					{{ html()->checkbox('objcts[]', false, 'imgcache')->class('js-check form-check-input') }}
				</div>
			</td>
			<td>
				Refresh Image Cache
			</td>
		</tr>

	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				&nbsp;
			</td>
		</tr>
	</tfoot>
</table>

