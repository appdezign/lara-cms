@if(!empty($data->duplicates))

	<h4 class="color-danger">{{ sizeof($data->duplicates) }} duplicates found in database!</h4>

	<div class="table-responsive">
		<table class="table table-lara table-row-bordered table-hover">
			<thead>
				<tr>
					<th class="w-5 show-large-up text-center">
						{{ _lanq('lara-admin::default.column.id') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.lang') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.cgroup') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.tag') }}
					</th>
					<th class="w-15">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.key') }}
					</th>
					<th class="w-45">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value') }}
					</th>
					<th class="w-5 text-center">
						<div class="d-none d-sm-block">
							{{ _lanq('lara-admin::default.button.edit') }}
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $data->duplicates as $dpl )

					<tr>
						<td class="text-center show-large-up">
							{{ $dpl->source->id }}
						</td>
						<td>
							{{ $dpl->source->language }}
						</td>
						<td>
							{{ $dpl->source->cgroup }}
						</td>
						<td>
							{{ $dpl->source->tag }}
						</td>
						<td>
							{{ $dpl->source->key }}
						</td>
						<td>
							{{ $dpl->source->value }}
						</td>

						<td class="text-center actionEdit">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $dpl->source->id]) }}"
							   data-token="{{ csrf_token() }}"
							   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}" data-method="delete">
								<i class="ion-ios-trash-outline"></i>
							</a>
						</td>
					</tr>
					<tr class="bg-lara-box-header">
						<td class="text-center show-large-up">
							{{ $dpl->duplicate->id }}
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							{{ $dpl->duplicate->value }}
						</td>

						<td class="text-center actionEdit">
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $dpl->duplicate->id]) }}"
							   data-token="{{ csrf_token() }}"
							   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}" data-method="delete">
								<i class="ion-ios-trash-outline"></i>
							</a>
						</td>
					</tr>
					<tr>
						<td colspan="7" class="trans-dupl-border">&nbsp;</td>
					</tr>
				@endforeach
			</tbody>

		</table>
	</div>

@else

	<div class="table-responsive">
		<table class="table table-lara table-row-bordered table-hover">
			<thead>
				<tr>
					<th class="w-5 show-large-up text-center">
						{{ _lanq('lara-admin::default.column.id') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.lang') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.cgroup') }}
					</th>
					<th class="w-10">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.tag') }}
					</th>
					<th class="w-15">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.key') }}
					</th>
					<th class="w-45">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value') }}
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

						<?php $tvalues = lang_from_db($obj->module, $obj->cgroup, $obj->tag, $obj->key); ?>

					<tr>
						<td class="text-center show-large-up">
							{{ $obj->id }}
						</td>
						<td>
							{{ $obj->language }}
						</td>
						<td>
							{{ $obj->cgroup }}
						</td>
						<td>
							{{ $obj->tag }}
						</td>
						<td>
							{{ $obj->key }}
						</td>
						<td>
							@if(!empty($obj->value))
								@if(starts_with($obj->value, '_'))
									<i class="fas fa-exclamation-circle color-danger"></i> {{ $obj->value }}
								@else
									{{ str_limit($obj->value, 50) }}
								@endif
							@else
								<i class="fas fa-exclamation-circle color-danger"></i>
							@endif
						</td>

						<td class="text-center action-icons">
							@if(empty($obj->locked_by))

								<a class="open-translation-modal"
								   data-bs-toggle="modal"
								   data-bs-target="#translationEditModal"
								   data-id="{{ $obj->id }}"
								   data-cgroup="{{ $obj->cgroup }}"
								   data-tag="{{ $obj->tag }}"
								   data-key="{{ $obj->key }}"
								   data-value="{{ $obj->value }}"
								   @foreach($tvalues as $tkey => $tvalue)
									   data-value_{{ $tkey }}="{{ $tvalue }}"
										@endforeach
								>
									<i class="las la-edit"></i>
								</a>
							@else
								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $obj->id]) }}"><i
											class="las la-lock"></i></a>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="8">
						@if($data->objects instanceof \Illuminate\Pagination\LengthAwarePaginator )
							{{ $data->objects->total() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
						@else
							{{ $data->objects->count() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
						@endif
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<div class="row">
		<div class="col-3 text-right">Files:</div>
		<div class="col-9">
			@foreach($data->tcount['file'] as $fkey => $fval)
				{{ $fkey }}: {{ $fval }}
				@if (!$loop->last)
					,&nbsp;
				@endif
			@endforeach
		</div>
	</div>
	<div class="row mb-5">
		<div class="col-3 text-right">Database:</div>
		<div class="col-9">
			@foreach($data->tcount['db'] as $fkey => $fval)
				{{ $fkey }}: {{ $fval }}
				@if (!$loop->last)
					,&nbsp;
				@endif
			@endforeach
		</div>
	</div>

@endif
