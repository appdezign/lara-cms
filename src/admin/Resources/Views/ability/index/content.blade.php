<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">&nbsp;</th>
				<th class="w-25">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.entity') }}
				</th>
				<th class="w-60">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.ability') }}
				</th>
				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.button.edit') }}
				</th>
				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.button.delete') }}
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $data->objects as $obj )
				<tr>
					<td class="text-center">&nbsp;</td>
					<td>{{ $obj->entity_key }}</td>
					<td>{{ $obj->name }}</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $obj->id]) }}"
							   class="">
								<i class="las la-edit"></i>
							</a>
						</td>
						<td class="text-center action-icons">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $obj->id]) }}"
							   data-token="{{ csrf_token() }}"
							   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}" data-method="delete">
								<i class="las la-trash"></i>
							</a>
						</td>

					@else
						<td colspan="2" class="text-center action-icons">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $obj->id]) }}"><i
										class="las la-lock"></i></a>
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
