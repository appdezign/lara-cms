<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">

	<x-closeleft>
		{{ route('admin.'.$entity->getEntityRouteKey().'.index') }}
	</x-closeleft>

	<div class="app-container container" id="kt_app_header_container">
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
							<div class="row">
								<div class="col-4 pt-3 text-end">
									Unlock:
								</div>
								<div class="col-4 pt-3">
									<div class="form-check">
										{{ html()->hidden('_enable_update', 0) }}
										{{ html()->checkbox('_enable_update', false, 1)->id('enable-update')->class('enable-update form-check-input') }}
									</div>
								</div>
								<div class="col-4">
									{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->id('globalsave')->class('btn btn-sm btn-danger save-button')->disabled() }}
								</div>
							</div>
						</div>
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
