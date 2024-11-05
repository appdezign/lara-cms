<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					<div class="form-check">
						{{ html()->checkbox('select_all', true, 1)->class('js-check-all  form-check-input')->id('check-all') }}
					</div>
				</th>
				</th>
				<th class="w-75">
					{{ _lanq('lara-admin::ga.column.type') }}
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
						{{ html()->checkbox('objcts[]', true, 'userstats')->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.user_stats') }}
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<div class="form-check">
						{{ html()->checkbox('objcts[]', true, 'browserstats')->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.browser_stats') }}
				</td>
			</tr>

			<tr>
				<td class="text-center">
					<div class="form-check">
						{{ html()->checkbox('objcts[]', true, 'sitestats')->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.site_stats') }}
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<div class="form-check">
						{{ html()->checkbox('objcts[]', true, 'pagestats')->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.page_stats') }}            </td>
			</tr>
			<tr>
				<td class="text-center">
					<div class="form-check">
						{{ html()->checkbox('objcts[]', true, 'refstats')->class('js-check form-check-input') }}
					</div>
				</td>
				<td>
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.ref_stats') }}
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
</div>

