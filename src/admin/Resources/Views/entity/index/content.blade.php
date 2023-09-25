<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					<div class="form-check form-check-batch">
						{{ html()->checkbox('select_all', false, 1)->id('check-all')->class('js-check-all  form-check-input') }}
					</div>
				</th>
				<th class="w-30">
					{{ _lanq('lara-admin::entity.column.title') }}
				</th>
				<th class="w-40">
					{{ _lanq('lara-admin::entity.column.group') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-admin::entity.column.menu') }}
				</th>
				<th class="w-5">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $data->objects as $obj )
				<tr>
					<td class="text-center">
						<div class="form-check">
							{{ html()->checkbox('objcts[]', false, $obj->id)->class('js-check form-check-input') }}
						</div>
					</td>
					<td>{{ $obj->title }}</td>
					<td>{{ $obj->egroup->key }}</td>
					<td>
						@if(!empty($obj->getMenuParent()))
							{{ $obj->getMenuParent() }}
						@endif @if(!empty($obj->getMenuPosition()))
							({{ $obj->getMenuPosition() }})
						@endif
					</td>

					<td class="text-center action-icons">
						<a href="{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $obj->id]) }}">
							<i class="far fa-cog"></i>
						</a>
					</td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">
					&nbsp;
				</td>
			</tr>
		</tfoot>
	</table>
</div>



