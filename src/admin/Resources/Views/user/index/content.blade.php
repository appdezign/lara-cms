<?php $rowclass = $data->showarchive ? 'greyText' : ''; ?>

<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 text-center">
					@if($entity->hasBatch())
						{{ html()->checkbox('select_all', false, 1)->id('check-all')->class('all form-control icheckjs') }}
					@else
						{{ _lanq('lara-admin::default.column.id') }}
					@endif
				</th>
				<th class="w-20">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.name') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.username') }}
				</th>
				<th class="w-20">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.email') }}
				</th>
				<th class="w-15">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.role') }}
				</th>
				<th class="w-10">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.level') }}
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
					<td class="text-center {{ $rowclass }}">
						@if($entity->hasBatch())
							{{ html()->checkbox('objcts[]', false, $obj->id)->class('check form-control icheckjs') }}
						@else
							{{ $obj->id }}
						@endif
					</td>
					<td class="{{ $rowclass }}">{{ $obj->name }}</td>
					<td class="{{ $rowclass }}">{{ $obj->username }}</td>
					<td class="{{ $rowclass }}">{{ $obj->email }}</td>
					<td class="{{ $rowclass }}">
						@foreach ($obj->roles()->pluck('name') as $role)
							{{ $role }}
						@endforeach
					</td>
					<td class="{{ $rowclass }}">{{ $obj->userlevel }}</td>

					@if($data->showarchive)

						<td class="{{ $rowclass }}">&nbsp;</td>
						<td class="{{ $rowclass }}">&nbsp;</td>

					@else

						@if(empty($obj->locked_by))

							@if($obj->userlevel > $mylevel)

								<td class="text-center action-icons">
									<div class="action-icon-disabled text-muted">
										<i class="las la-edit"></i>
									</div>
								</td>
								<td class="text-center action-icons">
									<div class="action-icon-disabled text-muted">
										<i class="las la-trash"></i>
									</div>
								</td>

							@else

								<td class="text-center action-icons">
									<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $obj->id]) }}"
									   class="">
										<i class="las la-edit"></i>
									</a>
								</td>
								<td class="text-center action-icons">
									<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $obj->id]) }}"
									   data-token="{{ csrf_token() }}"
									   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}"
									   data-method="delete">
										<i class="las la-trash"></i>
									</a>
								</td>

							@endif

						@else
							<td colspan="2" class="text-center action-icons">
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $obj->id]) }}">
									<i class="las la-lock"></i>
								</a>
							</td>
						@endif

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
