<?php

$imageDisks = array();
$videoDisks = array();
$fileDisks = array();

$disks = config('lara-admin.upload_disks.disks');

foreach ($disks as $disk) {
	$diskkey = $disk['key'];
	$diskname = $disk['name'];

	// available image disks
	if (in_array($diskkey, config('lara-admin.upload_disks.use_for_images'))) {
		$imageDisks[$diskkey] = $diskname;
	}

	// available video disks
	if (in_array($diskkey, config('lara-admin.upload_disks.use_for_videos'))) {
		$videoDisks[$diskkey] = $diskname;
	}

	// available video disks
	if (in_array($diskkey, config('lara-admin.upload_disks.use_for_files'))) {
		$fileDisks[$diskkey] = $diskname;
	}

}

?>

@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="media">
		{{ _lanq('lara-admin::entity.boxtitle.media') }}
	</x-boxheader>

	<div id="kt_card_collapsible_media" class="collapse show">
		<div class="box-body">

			{{-- MEDIA --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has images:', '_has_images') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_images', 0) }}
					{{ html()->checkbox('_has_images', $data->object->objectrelations->has_images, 1)->id('checkbox_has_images')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="media-container">

				<div class="row form-group my-5 max-media-slider">
					<div class="col-12 col-md-2">
						{{ html()->label('max images:', '_max_images') }}
					</div>

					<div class="col-9 col-md-7">
						<!-- noUiSlider - start -->
						<div id="kt_slider_max_images"
						     data-curval="{{ $data->object->objectrelations->max_images }}"></div>
						<!-- noUiSlider - end -->
					</div>
					<div class="col-3 col-md-2">
						<!-- noUiSlider value - start -->
						{{ html()->input('text', '_max_images', old('_max_images', $data->object->objectrelations->max_images))->id('kt_slider_max_images_input')->class('form-control')->isReadonly() }}
						<!-- noUiSlider value - end -->
					</div>

				</div>

				<div class="row form-group disk-media">
					<div class="col-12 col-md-2">
						{{ html()->label('image disk:', '_disk_images') }}
					</div>
					<div class="col-12 col-md-10 col-lg-9">
						<div class="select-two-xxxl">
							{{ html()->select('_disk_images', $imageDisks, $data->object->objectrelations->disk_images)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-6">
				<div class="col">
					<hr>
				</div>
			</div>

			{{-- VIDEO --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has videos:', '_has_videos') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_videos', 0) }}
					{{ html()->checkbox('_has_videos', $data->object->objectrelations->has_videos, 1)->id('checkbox_has_videos')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="video-container">
				<div class="row form-group my-5 max-videos-slider">
					<div class="col-12 col-md-2">
						{{ html()->label('max videos:', '_max_videos') }}
						</div>

					<div class="col-9 col-md-7">
						<!-- noUiSlider - start -->
						<div id="kt_slider_max_videos"
						     data-curval="{{ $data->object->objectrelations->max_videos }}"></div>
						<!-- noUiSlider - end -->
					</div>
					<div class="col-3 col-md-2">
						<!-- noUiSlider value - start -->
						{{ html()->input('text', '_max_videos', old('_max_videos', $data->object->objectrelations->max_videos))->id('kt_slider_max_videos_input')->class('form-control')->isReadonly() }}
						<!-- noUiSlider value - end -->
					</div>
				</div>

				<div class="row form-group disk-videos">
					<div class="col-12 col-md-2">
						{{ html()->label('video disk:', '_disk_videos') }}
					</div>
					<div class="col-12 col-md-10 col-lg-9">
						(embedded)
					</div>
				</div>
			</div>

			<div class="row disk-videos mb-6">
				<div class="col">
					<hr>
				</div>
			</div>

			{{-- VIDEO FILES --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has videofiles:', '_has_videofiles') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_videofiles', 0) }}
					{{ html()->checkbox('_has_videofiles', $data->object->objectrelations->has_videofiles, 1)->id('checkbox_has_videofiles')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="videofile-container mt-4">
				<div class="row form-group my-5 max-videofiles-slider">
					<div class="col-12 col-md-2">
						{{ html()->label('max videofiles:', '_max_videofiles') }}
					</div>
					<div class="col-9 col-md-7">
						<!-- noUiSlider - start -->
						<div id="kt_slider_max_videofiles"
						     data-curval="{{ $data->object->objectrelations->max_videofiles }}"></div>
						<!-- noUiSlider - end -->
					</div>
					<div class="col-3 col-md-2">
						<!-- noUiSlider value - start -->
						{{ html()->input('text', '_max_videofiles', old('_max_videofiles', $data->object->objectrelations->max_videofiles))->id('kt_slider_max_videofiles_input')->class('form-control')->isReadonly() }}
						<!-- noUiSlider value - end -->
					</div>

				</div>

				<div class="row form-group disk-videofiles">
					<div class="col-12 col-md-2">
						{{ html()->label('video disk:', '_disk_videofiles') }}
					</div>
					<div class="col-12 col-md-10 col-lg-9">
						<div class="select-two-xxxl">
							{{ html()->select('_disk_videos', $videoDisks, $data->object->objectrelations->disk_videos)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
						</div>
					</div>
				</div>
			</div>

			<div class="row disk-videofiles mb-6">
				<div class="col">
					<hr>
				</div>
			</div>

			{{-- FILES --}}
			<x-formrow>
				<x-slot name="label">
					{{ html()->label('has files:', '_has_files') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('_has_files', 0) }}
					{{ html()->checkbox('_has_files', $data->object->objectrelations->has_files, 1)->id('checkbox_has_files')->class('form-check-input') }}
				</div>
			</x-formrow>

			<div class="file-container">
				<div class="row form-group my-5 max-files-slider">
					<div class="col-12 col-md-2">
						{{ html()->label('max files:', '_max_files') }}
					</div>
					<div class="col-9 col-md-7">
						<!-- noUiSlider - start -->
						<div id="kt_slider_max_files"
						     data-curval="{{ $data->object->objectrelations->max_files }}"></div>
						<!-- noUiSlider - end -->
					</div>
					<div class="col-3 col-md-2">
						<!-- noUiSlider value - start -->
						{{ html()->input('text', '_max_files', old('_max_files', $data->object->objectrelations->max_files))->id('kt_slider_max_files_input')->class('form-control')->isReadonly() }}
						<!-- noUiSlider value - end -->
					</div>
				</div>

				<div class="row form-group disk-files">
					<div class="col-12 col-md-2">
						{{ html()->label('file disk:', '_disk_files') }}
					</div>
					<div class="col-12 col-md-10 col-lg-9">
						<div class="select-two-xxxl">
							{{ html()->select('_disk_files', $fileDisks, $data->object->objectrelations->disk_files)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
						</div>
					</div>
				</div>
			</div>

			<div class="row disk-files mb-6">
				<div class="col">
					<hr>
				</div>
			</div>

		</div>
	</div>

</div>


