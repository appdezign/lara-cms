<div class="box box-default collapsed-box">

	<div class="box-header with-border">

		<div class="box-tools box-tools-videofiles">
			<button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse"
			        data-bs-target="#kt_vfiles_collapsible_dropzone">
				<i class="fas fa-upload"></i> Upload
			</button>

			@if($data->object->videofiles->count() < $entity->getMaxVideoFiles())

				<div class="d-none d-md-block float-end">
					{{ html()->input('submit', '_save_image_upload', _lanq('lara-admin::default.button.save_videofiles'))->id('saveVideoFilesButton')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				<div class="d-block d-md-none float-end">
					{{ html()->input('submit', '_save_image_upload_mobile', _lanq('lara-admin::default.button.save'))->id('saveVideoFilesButtonMobile')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				{{ html()->input('submit', '_cancel_image_upload', _lanq('lara-admin::default.button.cancel'))->id('cancelVideoFilesButton')->class('btn btn-sm btn-outline btn-outline-success float-end')->style(['display' => 'none', 'margin-right' => '20px']) }}
			@endif

		</div>
	</div>

	<div id="kt_vfiles_collapsible_dropzone" class="collapse">
		@if($data->object->videofiles->count() < $entity->getMaxVideoFiles())
			<div id="dropzone-videofiles" class="box-body lara-dropzone">

				<div class="row mb-3">
					<div class="col-6 text-start">
						<span class="text-muted">format: {{ parseFileFormats(config('lara.upload_allowed_videofiles')) }}</span>
					</div>
					<div class="col-6 text-right">
						<span class="text-muted">max: {{ config('lara.upload_maxsize_videofile') }} MB</span>
					</div>
				</div>

				@if($data->object->videos()->count())
					{{ _lanq('lara-admin::default.message.object_has_embedded_video') }}
				@else
					<div class="dropzone2 needsclick dz-clickable">
						<div id="dz-preview-zone3" class="dropzone-previews">
							<div class="dz-message needsclick">
								{{ _lanq('lara-admin::default.button.dropvideofileshere') }}<br>
							</div>
						</div>
					</div>
				@endif

			</div>
		@else
			<div id="dropzone-videofiles" class="box-body lara-dropzone text-center">
				{{ _lanq('lara-admin::default.message.max_video_files_reached') }}
			</div>
		@endif
	</div>
</div>

@if (config('cloudflare-stream.accountId'))

	@include('lara-admin::_partials.cloudflare')

@else

	<div class="lara-file-list">

		@foreach( $data->object->videofiles as $vfile )

			<div class="box box-default">

				<div class="box-header with-border">
					<h3 class="box-title">Video</h3>
				</div>
				<div class="box-body">
					<div class="row py-3">
						<div class="col-12 col-sm-4 py-3 text-center">
							<div class="ratio ratio-16x9">
								<video controls>
									@if($data->object->publish == 1)
										<source src="{{ $entity->getUrlForVideos().'/' . $data->object->videofile->filename }}"
										        type="video/mp4">
									@else
										<source src="{{ $entity->getUrlForVideos().'/_archive/' . $data->object->videofile->filename }}"
										        type="video/mp4">
									@endif
								</video>
							</div>
						</div>

						<div class="col-4 text-center action-icons d-block d-sm-none">
							<a href="javascript:void(0)" class="float-end"
							   onclick="videofileDataToggler({{ $vfile->id }});">
								<i class="ion-compose"></i>
							</a>
						</div>
						<div class="col-4 text-center action-icons d-block d-sm-none">
							{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_videofile')->value('_delete_videofile_'.$vfile->id)->class('btn btn-link') }}
						</div>

						<div class="col-12 col-sm-6">
							{{-- FILE INFO PANEL--}}
							<div id="videofile_info_{{ $vfile->id }}" class="videofile-info-panel">

								<div class="row form-group">
									<div class="col-12 col-md-3">
										{{ _lanq('lara-admin::default.column.filename') }}
									</div>
									<div class="col-12 col-md-9 col-lg-9">
										@if($data->object->publish == 1)
											<a href="{{ $entity->getUrlForFiles() }}/{{ $vfile->filename }}"
											   target="_blank"
											   style="display:block;">
												{{ $vfile->filename }}
											</a>
										@else
											<a href="{{ $entity->getUrlForFiles() }}/_archive/{{ $vfile->filename }}"
											   target="_blank"
											   style="display:block;">
												{{ $vfile->filename }}
											</a>
										@endif
									</div>
								</div>

								<div class="row form-group">
									<div class="col-12 col-md-3 greyText">
										{{ _lanq('lara-admin::default.column.doctitle') }}
									</div>
									<div class="col-12 col-md-9 col-lg-9 greyText">
										{!!  $vfile->title  !!}
									</div>
								</div>
								<div class="image-tags">
									@if($vfile->featured == 1)
										<div class="image-tag">featured</div>
									@endif
								</div>

							</div>

							{{-- FILE EDIT PANEL--}}
							<div id="videofile_edit_{{ $vfile->id }}" class="videofile-edit-panel" style="display:none">

								<div class="row form-group">
									<div class="col">

										{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit', '_save_videofile')->value('_save_videofile_'.$vfile->id)->class('btn btn-sm btn-danger float-end') }}

										<a href="javascript:void(0)" class="btn btn-sm btn-outline btn-outline-success float-end me-3"
										   onclick="videofileDataToggler({{ $vfile->id }});">
											{{ _lanq('lara-admin::default.button.cancel') }}
										</a>

									</div>
								</div>

								<div class="row form-group">
									<div class="col-12 col-md-3">
										{{ html()->label('featured:', '_videofile_featured') }}
									</div>
									<div class="col-12 col-md-9">

										<div class="form-check">
											@if($vfile->featured == 1)
												{{ html()->hidden('_videofile_featured', 1) }}
												{{ html()->checkbox('_videofile_featured_' . $vfile->id, old('_videofile_featured_' . $vfile->id, $vfile->featured), 1)->class('form-check-input')->disabled() }}
											@else
												{{ html()->hidden('_videofile_featured', 0) }}
												{{ html()->checkbox('_videofile_featured_' . $vfile->id, old('_videofile_featured_' . $vfile->id, $vfile->featured), 1)->class('form-check-input') }}
											@endif
										</div>
									</div>
								</div>

								<div class="row form-group">
									<div class="col-12 col-md-3">
										{{ _lanq('lara-admin::default.column.filename') }}
									</div>
									<div class="col-12 col-md-9 col-lg-9">

										@if($data->object->publish == 1)
											<a href="{{ $entity->getUrlForFiles() }}/{{ $vfile->filename }}"
											   target="_blank"
											   style="display:block;">
												{{ $vfile->filename }}
											</a>
										@else
											<a href="{{ $entity->getUrlForFiles() }}/_archive/{{ $vfile->filename }}"
											   target="_blank"
											   style="display:block;">
												{{ $vfile->filename }}
											</a>
										@endif

									</div>
								</div>

								<div class="row form-group">
									<div class="col-12 col-md-3">
										{{ html()->label(_lanq('lara-admin::default.column.doctitle').':', '_doctitle_' . $vfile->id) }}
									</div>
									<div class="col-12 col-md-9">
										{{ html()->text('_doctitle_' . $vfile->id, $vfile->title)->class('form-control') }}
									</div>
								</div>

							</div>
						</div>

						<div class="col-1 text-center action-icons d-none d-sm-block">
							<a href="javascript:void(0)" onclick="videofileDataToggler({{ $vfile->id }});">
								<i class="las la-edit"></i>
							</a>
						</div>
						<div class="col-1 text-center action-icons d-none d-sm-block">
							{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_videofile')->value('_delete_videofile_'.$vfile->id)->class('btn btn-link') }}
						</div>
					</div>
				</div>
			</div>

		@endforeach
	</div>

@endif

