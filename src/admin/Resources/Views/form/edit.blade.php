@extends('lara-admin::layout2')

@section('content')

	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.'.$entity->entity_key.'.update', ['id' => $data->object->id]))
			->id('lara-builder-form')
			->attributes(['accept-charset' => 'UTF-8'])
			->class('needs-validation')
			->novalidate()
			->open() }}

	@includeFirst(['lara-admin::form.edit.header', 'lara-admin::entity.edit.header'])

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

								@includeFirst(['lara-admin::form.edit.content', 'lara-admin::entity.edit.content'])

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

		// show/hide sortable options
		const checkboxIsSortable = document.getElementById('entity_is_sortable');
		const sortableContainer = document.querySelector('.sortable-options');
		sortableContainer.style.display = (checkboxIsSortable.checked) ? 'none' : 'block';
		checkboxIsSortable.addEventListener('change', (event) => {
			sortableContainer.style.display = (event.currentTarget.checked) ? 'none' : 'block';
		});

		// show/hide group values
		const checkboxHasGroups = document.getElementById('checkbox_has_groups');
		const groupValues = document.querySelector('.groupValues');
		const checkboxHasTags = document.getElementById('checkbox_has_tags');
		const tagValues = document.querySelector('.tagValues');
		const checkboxHasFilters = document.getElementById('checkbox_has_filters');

		groupValues.style.display = (checkboxHasGroups.checked) ? 'block' :'none';
		tagValues.style.display = (checkboxHasTags.checked) ? 'block' :'none';

		checkboxHasGroups.addEventListener('change', (event) => {
			if (event.currentTarget.checked) {
				checkboxHasTags.checked = false;
				checkboxHasFilters.checked = false;
				tagValues.style.display = 'none';
				groupValues.style.display = 'block' ;
			} else {
				groupValues.style.display = 'none';
			}

		});

		checkboxHasTags.addEventListener('change', (event) => {
			if (event.currentTarget.checked) {
				checkboxHasGroups.checked = false;
				checkboxHasFilters.checked = false;
				groupValues.style.display = 'none';
				tagValues.style.display = 'block' ;
			} else {
				tagValues.style.display = 'none';
			}
		});

		checkboxHasFilters.addEventListener('change', (event) => {
			if (event.currentTarget.checked) {
				checkboxHasGroups.checked = false;
				checkboxHasTags.checked = false;
				groupValues.style.display = 'none';
				tagValues.style.display = 'none' ;
			}
		});

		// show/hide max media slider
		const checkboxHasMedia = document.getElementById('checkbox_has_images');
		const mediaContainer = document.querySelector('.media-container');
		mediaContainer.style.display = (checkboxHasMedia.checked) ? 'block' :'none';
		checkboxHasMedia.addEventListener('change', (event) => {
			mediaContainer.style.display = (event.currentTarget.checked) ? 'block' : 'none';
		});

		// show/hide max video slider
		const checkboxHasVideo = document.getElementById('checkbox_has_videos');
		const videoContainer = document.querySelector('.video-container');
		videoContainer.style.display = (checkboxHasVideo.checked) ? 'block' :'none';
		checkboxHasVideo.addEventListener('change', (event) => {
			videoContainer.style.display = (event.currentTarget.checked) ? 'block' : 'none';
		});

		// show/hide max video file slider
		const checkboxHasVideoFiles = document.getElementById('checkbox_has_videofiles');
		const videoFilesContainer = document.querySelector('.videofile-container');
		videoFilesContainer.style.display = (checkboxHasVideoFiles.checked) ? 'block' :'none';
		checkboxHasVideoFiles.addEventListener('change', (event) => {
			videoFilesContainer.style.display = (event.currentTarget.checked) ? 'block' : 'none';
		});

		// show/hide max files slider
		const checkboxHasFiles = document.getElementById('checkbox_has_files');
		const filesContainer = document.querySelector('.file-container');
		filesContainer.style.display = (checkboxHasFiles.checked) ? 'block' :'none';
		checkboxHasFiles.addEventListener('change', (event) => {
			filesContainer.style.display = (event.currentTarget.checked) ? 'block' : 'none';
		});

		// noUiSliders

		var slider1 = document.querySelector("#kt_slider_max_images");
		var sliderInput1 = document.querySelector("#kt_slider_max_images_input");
		var curVal1 = slider1.dataset.curval;
		noUiSlider.create(slider1, {
			start: curVal1,
			step: 1,
			connect: [true, false],
			tooltips: wNumb({decimals: 0}),
			range: {
				"min": 1,
				"max": 100
			}
		});
		slider1.noUiSlider.on("update", function (values, handle) {
			sliderInput1.value = Math.round(values[handle]);
		});

		var slider2 = document.querySelector("#kt_slider_max_videos");
		var sliderInput2 = document.querySelector("#kt_slider_max_videos_input");
		var curVal2 = slider2.dataset.curval;
		noUiSlider.create(slider2, {
			start: curVal2,
			step: 1,
			connect: [true, false],
			tooltips: wNumb({decimals: 0}),
			range: {
				"min": 1,
				"max": 100
			}
		});
		slider2.noUiSlider.on("update", function (values, handle) {
			sliderInput2.value = Math.round(values[handle]);
		});

		var slider3 = document.querySelector("#kt_slider_max_videofiles");
		var sliderInput3 = document.querySelector("#kt_slider_max_videofiles_input");
		var curVal3 = slider3.dataset.curval;
		noUiSlider.create(slider3, {
			start: curVal3,
			step: 1,
			connect: [true, false],
			tooltips: wNumb({decimals: 0}),
			range: {
				"min": 1,
				"max": 100
			}
		});
		slider3.noUiSlider.on("update", function (values, handle) {
			sliderInput3.value = Math.round(values[handle]);
		});

		var slider4 = document.querySelector("#kt_slider_max_files");
		var sliderInput4 = document.querySelector("#kt_slider_max_files_input");
		var curVal4 = slider4.dataset.curval;
		noUiSlider.create(slider4, {
			start: curVal4,
			step: 1,
			connect: [true, false],
			tooltips: wNumb({decimals: 0}),
			range: {
				"min": 1,
				"max": 100
			}
		});
		slider4.noUiSlider.on("update", function (values, handle) {
			sliderInput4.value = Math.round(values[handle]);
		});





		// show hide new custom view method
		$('#view_method_select').change(function () {
			if (this.value === 'custom') {
				$('#new_custom_method').show();
			} else {
				$('#new_custom_method').hide();
			}
		});

	</script>

	<script>

		const swalButton = document.querySelector('button.swal-save-builder-confirm');
		const form = document.getElementById('lara-builder-form');
		if(swalButton) {
			swalButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.loss_of_data') }}',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
					cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
				}).then((result) => {
					if (result.isConfirmed) {
						form.submit()
					}
				});
			});
		}

	</script>


@endsection

