@extends('lara-admin::layout2')

@section('content')

	{{ html()->form('POST', route('admin.'.$entity->getEntityRouteKey().'.store'))
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

								<div class="box box-default">
									<x-boxheader cstate="active" collapseid="content">
										{{ _lanq('lara-admin::default.boxtitle.content') }}
									</x-boxheader>
									<div id="kt_card_collapsible_content" class="collapse show">
										<div class="box-body">
											@include($data->partials['content'])
										</div>
									</div>
								</div>

							</div>

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

	{{ html()->form()->close() }}

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
				sliderInput.value = values[handle];
			});


		});

	</script>

@endsection
