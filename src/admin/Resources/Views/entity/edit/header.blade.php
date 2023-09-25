<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">

	<x-closeleft>
		{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $data->object->id]) }}
	</x-closeleft>

	<div class="app-container container" id="kt_app_header_container">
		<div class="app_header_wrapper" id="kt_app_header_wrapper">

			<div class="row">
				<div class="col-11 col-md-10 offset-md-1">
					<div class="d-flex flex-row-reverse gap-3 align-items-center h-100">

						@if(!$data->maxColumnsReached)

							@if(!$data->entityLocked)
								{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-sm btn-danger float-end swal-save-builder-confirm') }}
							@else
								{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-sm btn-danger float-end swal-save-builder-confirm')->disabled() }}
							@endif

							@if($data->totalCount > 0)
								@if($data->force == true)
									<a href="{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id, 'force' => 'false']) }}"
									   class="btn btn-sm btn-icon btn-outline btn-outline-success">
										<i class="las la-unlock"></i>
									</a>
								@else
									<a href="{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id, 'force' => 'true']) }}"
									   class="btn btn-sm btn-icon btn-outline btn-outline-danger">
										<i class="las la-lock"></i>
									</a>
								@endif
							@endif

						@else

							{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-sm btn-danger float-end swal-save-builder-confirm')->disabled() }}

							<div class="pull-right" style="margin-right:20px; padding: 6px;">
								<x-badge placement="bottom">
									<x-slot name="title">
										Locked: too many fields ({{ $data->object->customcolumns->count() }})
									</x-slot>
									This entity is locked, because it has reached the maximum number of custom columns ({{ config('lara-eve.builder_custom_columns_max') }}). Add fields to the database manually, if necessary.
								</x-badge>
							</div>

						@endif

					</div>

				</div>
			</div>

		</div>
	</div>

	<x-closeright>
		{{ route('admin.'.$entity->getEntityRouteKey().'.unlock', ['id' => $data->object->id]) }}
	</x-closeright>
</div>
<!--end::Header-->
