<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">

	<x-closeleft>
		{{ route('admin.'.$entity->getEntityRouteKey().'.index') }}
	</x-closeleft>

	<div class="app-container container" id="kt_app_header_container">
		<div class="app_header_wrapper" id="kt_app_header_wrapper">

			<div class="row">
				<div class="col-11 col-md-10 offset-md-1">
					<div class="d-flex flex-row-reverse gap-3 align-items-center h-100">
						{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-sm btn-danger float-end') }}
					</div>

				</div>
			</div>

		</div>
	</div>

	<x-closeright>
		{{ route('admin.'.$entity->getEntityRouteKey().'.index') }}
	</x-closeright>
</div>
<!--end::Header-->
