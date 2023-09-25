@extends('lara-admin::layout2')

@section('content')


	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.'.$entity->getEntityRouteKey().'.savetaggable', ['id' => $data->object->id]))
			->attributes(['accept-charset' => 'UTF-8'])
			->open() }}

	@include($data->partials['header'])

	<!--begin::Wrapper-->
	<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
		<!--begin::Main-->
		<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
			<!--begin::Content wrapper-->
			<div class="d-flex flex-column flex-column-fluid">

				@include($data->partials['pagetitle'])

				<!--begin::Content-->
				<div id="kt_app_content" class="app-content  flex-column-fluid">
					<!--begin::Content container-->
					<div id="kt_app_content_container" class="app-container container">

						<div class="row">
							<div class="col-12 col-lg-10 offset-lg-1">

								@include($data->partials['content'])

							</div>
						</div>

					</div>
					<!--end::Content container-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Content wrapper-->
		</div>
		<!--end:::Main-->
	</div>
	<!--end::Wrapper-->

	{{ html()->hidden('language', $clanguage) }}

	{{ html()->closeModelForm() }}

@endsection

@section('scripts-after')

	<script>
		$(document).ready(function () {

			// check all
			const enableUpdate = document.querySelector('input.enable-update');
			const checkboxes = document.querySelectorAll('input.js-check');
			const globalSaveButton = document.querySelector('#globalsave');

			enableUpdate.addEventListener('change', (event) => {
				if (event.currentTarget.checked) {
					checkboxes.forEach((chbx) => {
						chbx.disabled = false;
					});
					globalSaveButton.disabled = false;
				} else {
					checkboxes.forEach((chbx) => {
						chbx.disabled = true;
					});
					globalSaveButton.disabled = true;
				}
			});

		});

	</script>

@endsection