<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.column.id') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-admin::'.$entity->getEntityKey().'.column.title') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-admin::'.$entity->getEntityKey().'.column.key') }}
				</th>
				<th class="w-50">
					{{ _lanq('lara-admin::'.$entity->getEntityKey().'.column.path') }}
				</th>
				<th class="w-5 text-center">
					<div class="d-none d-sm-block">
						{{ _lanq('lara-admin::default.button.edit') }}
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $data->objects as $obj )

				<tr>
					<td class="text-center">
						{{ $obj->id }}
					</td>

					<td>{{ $obj->title }}</td>
					<td>{{ $obj->key }}</td>
					<td>{{ $obj->path }}</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">
							<a href="{{ route('admin.'.$entity->getEntityKey().'.edit', ['id' => $obj->id]) }}"
							   class="">
								<i class="las la-edit"></i>
							</a>
						</td>

					@else
						<td class="text-center action-icons">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $obj->id]) }}">
								<i class="las la-lock"></i>
							</a>
						</td>
					@endif
				</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<div class="d-flex justify-content-between">
						@include('lara-admin::_partials.index_footer')
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

@if($data->params->paginate)
	{{ $data->objects->links('lara-admin::_partials.pagination') }}
@endif