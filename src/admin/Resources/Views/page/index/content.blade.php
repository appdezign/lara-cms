<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 d-none d-md-table-cell text-center">
					@if($entity->hasBatch())
						@can('update', $entity->getEntityModelClass())
							<div class="form-check">
							{{ html()->checkbox('select_all', false, 1)->id('check-all')->class('js-check-all form-check-input') }}
							</div>
						@else
							{{ _lanq('lara-admin::default.column.id') }}
						@endcan
					@else
						{{ _lanq('lara-admin::default.column.id') }}
					@endif
				</th>
				<th class="w-5 text-center">
					@if($entity->hasStatus())
						{{ _lanq('lara-admin::default.column.publish') }}
					@else
						&nbsp;
					@endif
				</th>

				<th class="w-60">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
				</th>

				<th class="w-10 d-none d-md-table-cell">
					&nbsp;
				</th>
				<th class="w-10 d-none d-md-table-cell">
					@if($entity->hasTags())
						{{ _lanq('lara-admin::default.column.tags') }}
					@elseif($entity->hasGroups())
						{{ _lanq('lara-admin::default.column.group') }}
					@elseif($entity->getShowAuthor())
						{{ _lanq('lara-admin::default.column.author') }}
					@else
						&nbsp;
					@endif
				</th>

				<th class="w-5 text-center">
					@if($entity->getEgroup() == 'form')
						{{ _lanq('lara-admin::default.button.view') }}
					@else
						{{ _lanq('lara-admin::default.button.edit') }}
					@endif
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
						@if($entity->hasBatch())
							@can('update', $obj)
								<div class="form-check">
									{{ html()->checkbox('objcts[]', false, $obj->id)->class('js-check form-check-input') }}
								</div>
							@else
								{{ $obj->id }}
							@endcan
						@else
							{{ $obj->id }}
						@endif
					</td>
					<td class="text-center status">
						@if($entity->hasStatus())
							@if(isset($obj->sticky) && $obj->sticky == 1)
								<i class="las la-sticky-note status-sticky color-danger"></i>
							@else

								@if($obj->publish == 1)
									@if($entity->hasHideinlist() && $obj->publish_hide == 1)
										<i class="las la-minus-circle status-hide"></i>
									@else
										<i class="las la-check-circle status-publish"></i>
									@endif
								@else
									<i class="las la-circle status-concept"></i>
								@endif
							@endif
						@else
							&nbsp;
						@endif
					</td>
					<td>
						<div class="text-muted d-block d-md-none">
							@if($obj->menuroute)
								{{ $obj->menuroute }}
							@else
								[-]
							@endif
						</div>
						<div class="text-muted d-none d-md-inline-block">
							@if($obj->menuroute)
								{{ $obj->menuroute }}
							@else
								[-]
							@endif
						</div>
						<a href="{{ route('content.page.show', ['id' => $obj->id]) }}"
						   target="_blank">{{ str_limit($obj->title, 40) }}</a>

					</td>
					<td class="media-icons d-none d-md-table-cell">
						@if($entity->hasImages() && $obj->media->count())
							<i class="far fa-file-image"></i>
						@endif
						@if($entity->hasFiles() && $obj->files->count())
							<i class="far fa-file-alt"></i>
						@endif
						@if($entity->hasVideos() && $obj->videos->count())
							<i class="fab fa-youtube"></i>
						@endif
						@if($entity->hasVideoFiles() && $obj->videofiles->count())
							<i class="far fa-video"></i>
						@endif
					</td>
					<td class="d-none d-md-table-cell">
						@if($entity->hasTags())
							@foreach($obj->tags as $tag)
								@if ($loop->first)
									{{ $tag->title }}
								@endif
								@if ($loop->iteration == 2)
									, {{ $tag->title }}
								@endif
								@if ($loop->iteration == 3)
									, <span class="greyText">[...]</span>
								@endif
							@endforeach
						@elseif($entity->hasGroups())
							{{ $obj->cgroup }}
						@elseif($entity->getRelationFilterForeignkey())
								<?php $relEntKey = $entity->getRelationFilterEntitykey(); ?>
							{{ $obj->$relEntKey->title }}
						@elseif($entity->getShowAuthor())
							{{ $obj->user->username }}
						@else
							&nbsp;
						@endif
					</td>

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">
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
						</td>
						<td class="text-center action-icons">
							@can('delete', $obj)
								@if(substr($obj->menuroute,0,1) == '/')
									<div class="action-icon-disabled text-muted">
										<i class="las la-trash"></i>
									</div>
								@else
									<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $obj->id]) }}"
									   data-token="{{ csrf_token() }}"
									   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}"
									   data-method="delete">
										<i class="las la-trash"></i>
									</a>
								@endif
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

