<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">

	<x-closeleft>
		{{ route('admin.'.$data->related->getEntityKey().'.index') }}
	</x-closeleft>

	<div class="app-container container-xxl" id="kt_app_header_container">
		<div class="app_header_wrapper" id="kt_app_header_wrapper">

			<div class="row">
				<div class="col-11 col-md-10 offset-md-1">
					<div class="d-flex justify-content-between align-items-center h-100">

						@if(isset($clanguage))
							<div class="clang">
								{{ strtoupper($clanguage) }}
							</div>
						@endif

						<div class="action-buttons">
							<a class="btn btn-sm btn-icon btn-outline btn-outline-primary me-2"
							   data-bs-toggle="modal" data-bs-target="#tagCreateModal">
								<i class="fal fa-plus"></i>
							</a>
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.reorder', ['entity' => $data->related->getEntityKey(), 'taxonomy' => $data->taxonomy->slug]) }}"
							   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
							   title="{{ _lanq('lara-admin::default.button.reorder') }}">
								<i class="far fa-arrows"></i>
							</a>
						</div>
					</div>

				</div>
			</div>

		</div>
	</div>

	<x-closeright>
		{{ route('admin.'.$data->related->getEntityKey().'.index') }}
	</x-closeright>
</div>
<!--end::Header-->
