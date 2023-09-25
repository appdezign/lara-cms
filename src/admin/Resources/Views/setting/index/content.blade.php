<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-30">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.group') }}
				</th>
				<th class="w-30">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value') }}
				</th>
				<th class="w-10">
					&nbsp;
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

				@if($obj->cgroup == 'system')
					<tr>
						<td class="text-muted">{{ $obj->title }}</td>
						<td class="text-muted">{{ $obj->cgroup }}</td>
						<td class="text-muted">{{ str_limit($obj->value, 40, ' ...') }}</td>
						<td>
							<div class="locked-icon">
								@if($obj->locked_by_admin == 1)
									<i class="las la-lock" style></i>
								@endif
							</div>
						</td>
						<td colspan="2">&nbsp;</td>
					</tr>
				@else
					<tr>
						<td>{{ $obj->title }}</td>
						<td>{{ $obj->cgroup }}</td>
						<td>{{ str_limit($obj->value, 40, ' ...') }}</td>
						<td>
							<div class="locked-icon">
								@if($obj->locked_by_admin == 1)
									<i class="las la-lock" style></i>
								@endif
							</div>
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
				@endif
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
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
