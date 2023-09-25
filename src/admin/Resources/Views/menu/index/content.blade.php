<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.column.id') }}
				</th>
				<th class="w-30">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
				</th>
				<th class="w-55">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.slug') }}
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
					<td class="text-center">
						@if($entity->hasBatch())
							{{ html()->checkbox('objcts[]', false, $obj->id)->class('check form-control icheckjs') }}
						@else
							{{ $obj->id }}
						@endif
					</td>

					<td>{{ $obj->title }}</td>
					<td>{{ $obj->slug }}</td>

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
							@if($obj->count == 0)
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
							@endif
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
				<td colspan="6">
					&nbsp;
				</td>
			</tr>
		</tfoot>
	</table>
</div>
