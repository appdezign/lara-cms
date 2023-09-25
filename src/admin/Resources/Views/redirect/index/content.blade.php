<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.column.publish') }}
				</th>
				<th class="w-30">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirectfrom') }}
				</th>
				<th class="w-5 text-center">
					&nbsp;
				</th>
				<th class="w-30">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirectto') }}
				</th>
				<th class="w-10">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirecttype') }}
				</th>
				<th class="w-10">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.source') }}
				</th>
				<th class="w-5 text-center">
					<div class="d-none d-sm-block">
						{{ _lanq('lara-admin::default.button.edit') }}
					</div>
				</th>
				<th class="w-5 text-center">
					<div class="d-none d-sm-block">
						{{ _lanq('lara-admin::default.button.delete') }}
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $data->objects as $obj )

				<tr>
					<td class="text-center status">
						@if($obj->publish == 1)
							<i class="las la-check-circle status-publish"></i>
						@else
							<i class="las la-circle status-concept"></i>
						@endif
					</td>

					<td>
						<a href="{{ url($clanguage . '/' . $obj->redirectfrom) }}" target="_blank">
							{{ $obj->redirectfrom }}
						</a>
					</td>
					<td class="text-center">
						@if($obj->has_error)
							<i class="fal fa-exclamation-circle color-danger"></i>
						@else
							<i class="fal fa-caret-right"></i>
						@endif
					</td>
					<td>
						<a href="{{ url($clanguage . '/' . $obj->redirectto) }}" target="_blank">
							{{ $obj->redirectto }}
						</a>
					</td>
					<td>
						{{ $obj->redirecttype }}
					</td>
					<td>
						@if($obj->auto_generated)
							auto
						@else
							manual
						@endif
					</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">

							@if($entity->getEgroup() == 'form')
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.show', ['id' => $obj->id]) }}"
								   class="">
									<i class="las la-eye"></i>
								</a>
							@else
								@can('update', $obj)
									<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $obj->id]) }}"
									   class="">
										<i class="las la-edit"></i>
									</a>
								@else
									<div class="action-icon-disabled text-muted">
										<i class="las la-edit"></i>
									</div>
								@endcan
							@endif
						</td>
						<td class="text-center action-icons">
							@can('delete', $obj)
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $obj->id]) }}"
								   data-token="{{ csrf_token() }}"
								   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}"
								   data-method="delete">
									<i class="las la-trash"></i>
								</a>
							@else
								<div class="action-icon-disabled text-muted">
									<i class="las la-trash"></i>
								</div>
							@endcan
						</td>

					@else
						<td colspan="2" class="text-center action-icons">
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
				<td colspan="8">
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
