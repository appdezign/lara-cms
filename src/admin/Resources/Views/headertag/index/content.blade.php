<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 d-none d-md-table-cell text-center">
					{{ _lanq('lara-admin::default.column.id') }}
				</th>

				<th class="w-35">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
				</th>
				<th class="w-10 d-none d-lg-table-cell">
					Title Tag
				</th>
				<th class="w-10 d-none d-lg-table-cell">
					List Tag
				</th>
				<th class="w-20 d-none d-lg-table-cell">
					{{ _lanq('lara-admin::default.column.group') }}
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
					<td class="d-none d-md-table-cell text-center">
						{{ $obj->id }}
					</td>

					<td>
						<span class="color-primary">{{ str_limit($obj->title, 40) }}</span>
					</td>

					<td class="media-icons d-none d-lg-table-cell">
						{{ $obj->title_tag }}
					</td>
					<td class="media-icons d-none d-lg-table-cell">
						{{ $obj->list_tag }}
					</td>

					<td class="d-none d-lg-table-cell">
						{{ $obj->cgroup }}
						@if($obj->cgroup == 'module' && !empty($obj->entity_id))
							- {{ $obj->entity->entity_key }}
						@elseif($obj->cgroup == 'larawidget' && !empty($obj->larawidget_id))
							- {{ $obj->larawidget->title }}
						@endif
					</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">
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
