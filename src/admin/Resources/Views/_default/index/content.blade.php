<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">
		<thead>
			<tr>
				<th class="w-5 d-none d-md-table-cell text-center">
					@if($entity->hasBatch())
						@can('update', $entity->getEntityModelClass())
							<div class="form-check form-check-batch">
								{{ html()->checkbox('select_all', false, 1)->id('check-all')->class('js-check-all  form-check-input') }}
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
				<th class="w-15 d-none d-md-table-cell">
					{{ _lanq('lara-admin::default.column.created_at') }}
				</th>

				@if($entity->getEgroup() == 'form')
					<th class="w-65">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
					</th>
				@else
					<th class="w-35">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
					</th>
					<th class="w-10 d-none d-lg-table-cell">
						&nbsp;
					</th>
					<th class="w-20 d-none d-lg-table-cell">
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
				@endif

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
					<td class="d-none d-md-table-cell">

							<?php $datefieldfound = null; ?>
						@foreach($entity->getCustomColumns() as $field)
							@if($field->primary == 1 && ($field->fieldtype == 'date' || $field->fieldtype == 'datetime'))
									<?php
									$fieldvarname = $field->fieldname;
									$datefieldfound = $fieldvarname;
									?>
								{{ Date::parse($obj->$fieldvarname)->format('d M Y') }}
							@endif
						@endforeach

						@if(empty($datefieldfound))
							@if(!empty($obj->publish_from))
								{{ Date::parse($obj->publish_from)->format('d M Y') }}
							@else
								{{ Date::parse($obj->created_at)->format('d M Y') }}
							@endif
						@endif
					</td>

					@if($entity->getEgroup() == 'form')
						<td>
							<span class="color-primary">{{ str_limit($obj->title, 40) }}</span>
							<span class="text-muted">
							@if($entity->getEgroup() == 'form')
								@foreach($entity->getCustomColumns() as $field)
									@if($field->primary == 1)
											<?php $fieldvarname = $field->fieldname; ?>
										&dash; <em>{{ str_limit($obj->$fieldvarname, $limit = 80, $end = '...') }}</em>
									@endif
								@endforeach
								@endif
							</span>

						</td>
					@else

						<td>

							<span class="text-muted">
								@foreach($entity->getCustomColumns() as $field)
									@if(($field->fieldhook == 'before' || $field->fieldhook == 'between') && $field->primary == 1 && $field->fieldname != $datefieldfound)
											<?php $fieldvarname = $field->fieldname; ?>
										{{ $obj->$fieldvarname }}
									@endif
								@endforeach
							</span>

							@if(config('lara.has_frontend'))

								@if($entity->getEgroup() == 'entity')
									@if($entity->hasTags())
										<a href="{{ route('contenttag.'.$entity->getEntityKey().'.index.show', ['id' => $obj->id]) }}"
										   target="_blank">{{ str_limit($obj->title, 40) }}</a>
									@else
										<a href="{{ route('content.'.$entity->getEntityKey().'.index.show', ['id' => $obj->id]) }}"
										   target="_blank">{{ str_limit($obj->title, 40) }}</a>
									@endif
								@else
									<span class="color-primary">{{ str_limit($obj->title, 40) }}</span>
								@endif

							@else

								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.show', ['id' => $obj->id]) }}">{{ str_limit($obj->title, 40, ' ...') }}</a>

							@endif

							<span class="text-muted">
							@foreach($entity->getCustomColumns() as $field)
									@if($field->fieldhook == 'after' && $field->primary == 1)
											<?php $fieldvarname = $field->fieldname; ?>
										{{ $obj->$fieldvarname }}
									@endif
								@endforeach
						</span>
						</td>

						<td class="media-icons d-none d-lg-table-cell">
							@if($entity->hasImages() && $obj->media->count())
								<i class="fal fa-file-image"></i>
							@endif
							@if($entity->hasFiles() && $obj->files->count())
								<i class="fal fa-file-alt"></i>
							@endif
							@if($entity->hasVideos() && $obj->videos->count())
								<i class="fab fa-youtube"></i>
							@endif
							@if($entity->hasVideoFiles() && $obj->videofiles->count())
								<i class="fal fa-video"></i>
							@endif
						</td>
						<td class="d-none d-lg-table-cell">
							@if($entity->hasTags())
								@foreach($obj->tags as $tag)
									@if ($loop->first)
										{{ $tag->title }}
									@endif
									@if ($loop->iteration == 2)
										, {{ $tag->title }}
									@endif
									@if ($loop->iteration == 3)
										, <span class="text-muted">[...]</span>
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
					@endif

					@if(empty($obj->locked_by))

						<td class="text-center action-icons">

							@if($entity->getEgroup() == 'form')
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.show', ['id' => $obj->id]) }}" >
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
