<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">

	<x-closeleft>
		{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id]) }}
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

					</div>

				</div>
			</div>

		</div>
	</div>

	<x-closeright>
		{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id]) }}
	</x-closeright>
</div>
<!--end::Header-->



