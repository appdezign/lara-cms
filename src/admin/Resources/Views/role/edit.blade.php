@extends('lara-admin::layout2')

@section('content')

	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.'.$entity->getEntityRouteKey().'.update', ['id' => $data->object->id]))
			->id('lara-default-edit-form')
			->attributes(['accept-charset' => 'UTF-8'])
			->class('needs-validation')
			->novalidate()
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

	{{ html()->closeModelForm() }}

@endsection

@section('scripts-after')

	<script type="text/javascript">

		$(document).ready(function () {

			// slider (noUiSlider)
			var slider = document.querySelector("#kt_slider_level");
			var sliderInput = document.querySelector("#kt_slider_level_input");
			var curVal = slider.dataset.curval;
			noUiSlider.create(slider, {
				start: curVal,
				step: 10,
				connect: [true, false],
				tooltips: wNumb({decimals: 0}),
				range: {
					"min": 10,
					"max": 100
				}
			});
			slider.noUiSlider.on("update", function (values, handle) {
				sliderInput.value = Math.round(values[handle]);
			});


			// check all (JS)
			@foreach($data->entkeys as $entkey)

			const {{ $entkey }}CheckAll = document.querySelector('input.{{ $entkey }}_all');
			const {{ $entkey }}CheckBoxes = document.querySelectorAll('input.{{ $entkey }}_check');

			{{ $entkey }}CheckAll.addEventListener('change', (event) => {
				if (event.currentTarget.checked) {
					{{ $entkey }}CheckBoxes.forEach((chbx) => {
						chbx.checked = true;
					});
				} else {
					{{ $entkey }}CheckBoxes.forEach((chbx) => {
						chbx.checked = false;
					});
				}
			});

			{{ $entkey }}CheckBoxes.forEach((item) => {
				item.addEventListener('change', function (event) {
					let checkedCount = 0;
					{{ $entkey }}CheckBoxes.forEach((chbx) => {
						if (chbx.checked) {
							checkedCount++;
						}
					});
					if (checkedCount < {{ $entkey }}CheckBoxes.length) {
						{{ $entkey }}CheckAll.checked = false;
					} else {
						{{ $entkey }}CheckAll.checked = true;
					}
				});
			});
			@endforeach

		});

	</script>

@endsection

