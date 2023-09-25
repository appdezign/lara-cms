<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 show-large-up text-center">
					{{ _lanq('lara-admin::default.column.id') }}
				</th>

				<th class="w-5 text-center">
					{{ _lanq('lara-admin::default.column.publish') }}
				</th>
				<th class="w-15 show-medium-up">
					{{ _lanq('lara-admin::default.column.created_at') }}
				</th>
				<th class="w-25">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
				</th>
				<th class="w-10 show-large-up">
					&nbsp;
				</th>
				<th class="w-10 show-large-up">
					Type
				</th>
				<th class="w-10 show-large-up">
					Hook
				</th>
				<th class="w-10 show-large-up">
					Module
				</th>
				<th class="w-5 text-center">
					<div class="d-none d-sm-block">
						{{ _lanq('lara-admin::default.button.edit') }}
					</div>
				</th>
				<th class="col-5 text-center">
					<div class="d-none d-sm-block">
						{{ _lanq('lara-admin::default.button.delete') }}
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $data->objects as $obj )

				<tr>
					<td class="text-center show-large-up">
						{{ $obj->id }}
					</td>

					<td class="text-center status">
						@if($obj->publish == 1)
							<i class="las la-check-circle status-publish"></i>
						@else
							<i class="las la-circle status-concept"></i>
						@endif
					</td>

					<td class="show-medium-up">
						@if(!empty($obj->publish_from))
							{{ Date::parse($obj->publish_from)->format('d M Y') }}
						@else
							{{ Date::parse($obj->created_at)->format('d M Y') }}
						@endif
					</td>
					<td>
						<span class="color-primary">{{ str_limit($obj->title, 40) }}</span>
					</td>

					<td class="show-large-up">
						@if($entity->hasImages() && $obj->media->count())
							<i class="fa fa-file-image-o" aria-hidden="true"></i>
						@endif
						@if($entity->hasFiles() && $obj->files->count())
							<i class="fa fa-file-text-o" aria-hidden="true"></i>
						@endif
					</td>

					<td class="show-large-up">
						{{ $obj->type }}
					</td>
					<td class="show-large-up">
						{{ $obj->hook }}
					</td>
					<td class="show-large-up">
						{{ $obj->relentkey }}
					</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">

							@if($entity->getEgroup() == 'form')
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.show', ['id' => $obj->id]) }}"
								   class="">
									<i class="las la-eye"></i>
								</a>
							@else
								@if($entity->hasSync() && $obj->sync()->count())
									@if(Auth::user()->isAn('administrator'))
										<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $obj->id]) }}"
										   class="">
											<i class="las la-sign-out-alt"></i>
										</a>
									@else
										<div class="action-icon-disabled text-muted">
											<i class="las la-sign-out-alt"></i>
										</div>
									@endif
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
				<td colspan="10">
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
