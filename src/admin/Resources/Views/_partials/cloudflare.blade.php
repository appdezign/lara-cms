<div class="lara-file-list cf-lara-file-list">
	@foreach( $data->object->videofiles as $vfile )

			<?php

			$thumbParams1 = [
				'time'   => $vfile->cfs_thumb_offset . 's',
				'width'  => 640,
				'height' => 360,
			];
			$thumbnail = 'https://videodelivery.net/' . $vfile->cfs_uid . '/thumbnails/thumbnail.jpg?' . http_build_query($thumbParams1);

			$thumbParams2 = [
				'time' => $vfile->cfs_thumb_offset . 's',
			];
			$thumb = 'https://videodelivery.net/' . $vfile->cfs_uid . '/thumbnails/thumbnail.jpg?' . http_build_query($thumbParams2);

			$videoParams = [
				'poster' => $thumb,
			];

			$videoUrl = 'https://iframe.videodelivery.net/' . $vfile->cfs_uid . '?' . http_build_query($videoParams)

			?>

		<div class="box box-default">

			<div class="box-header with-border collapsible cursor-pointer rotate active"
			     data-bs-toggle="collapse"
			     data-bs-target="#kt_card_collapsible_vidoriginal">

				<h3 class="box-title">Video #{{ $vfile->id }}</h3>

				<div class="card-toolbar rotate-180">
					<span class="svg-icon svg-icon-1">
						@include('lara-admin::_icons.arrowdown_svg')
					</span>
				</div>

			</div>

			<div class="box-body p-6">

				<div class="box box-default">

					<div class="box-header cf-box-header with-border">
						<h3 class="box-title">Original</h3>
					</div>
					<div class="box-body">
						<div class="row py-2">
							<div class="col-12 col-sm-4 py-2 text-center">
								@if(in_array($data->object->videofile->mimetype, config('lara.web_video_formats')))
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
								@else
									<div class="video-no-preview">
										<p>Cannot preview<br>
											this video format:</p>
										<p>{{ $data->object->videofile->mimetype }}</p>
									</div>
								@endif

							</div>

							<div class="col-4 text-center action-icons d-block d-sm-none">
								<a href="javascript:void(0)" class="float-end"
								   onclick="videofileDataToggler({{ $vfile->id }});">
									<i class="las la-edit"></i>
								</a>
							</div>
							<div class="col-4 text-center action-icons d-block d-sm-none">
								{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_videofile')->value('_delete_videofile_'.$vfile->id)->class('btn btn-link') }}
							</div>

							<div class="col-12 col-sm-6">
								{{-- FILE INFO PANEL--}}
								<div id="file_info_{{ $vfile->id }}" class="file-info-panel cf-file-info-panel">

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
										<div class="col-12 col-md-3 text-muted">
											{{ _lanq('lara-admin::default.column.doctitle') }}
										</div>
										<div class="col-12 col-md-9 col-lg-9 text-muted">
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
								<div id="file_edit_{{ $vfile->id }}" class="file-edit-panel" style="display:none">

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
											{{ html()->hidden('_videofile_featured', 0) }}

											<div class="form-check">

												@if($vfile->featured == 1)
													{{ html()->checkbox('_videofile_featured_' . $vfile->id, old('_videofile_featured_' . $vfile->id, $vfile->featured), 1)->class('form-check-input')->disabled() }}
												@else
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

				<div class="box box-default">

					<div class="box-header cf-box-header with-border">
						<h3 class="box-title">CDN</h3>
					</div>

					<div class="box-body">

						<div class="row mb-6">
							<div class="col-6">

								<div class="row form-group">
									<div class="col-6">
										<h4 class="mt-0 mb-6">CDN Status</h4>
									</div>
									<div class="col-6">
										<a href="{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id]) }}">refresh</a>
									</div>
								</div>

								<div class="row form-group">
									<div class="col-6">
										upload to CDN
									</div>
									<div class="col-6">

										@if(empty($vfile->cfs_uid))
											<i class="fa fa-lg fa-cog fa-spin"></i>
										@else
											<i class="fa fa-lg fa-check-circle" aria-hidden="true"></i>
										@endif
									</div>
								</div>
								<div class="row form-group">
									<div class="col-6">
										process for web
									</div>
									<div class="col-6">
										@if(empty($vfile->cfs_uid))
											<i class="fa fa-lg fa-clock-o" aria-hidden="true"></i>
										@else
											@if($vfile->cfs_ready == 0)
												<i class="fa fa-lg fa-cog fa-spin"></i>
											@else
												<i class="fa fa-lg fa-check-circle" aria-hidden="true"></i>
											@endif
										@endif
									</div>
								</div>

								<div class="row form-group mb-10">
									<div class="col-6">
										ready for streaming
									</div>
									<div class="col-6">
										@if($vfile->cfs_ready == 0)
											<i class="fa fa-lg fa-clock-o" aria-hidden="true"></i>
										@else
											<i class="fa fa-lg fa-check-circle" aria-hidden="true"></i>
										@endif
									</div>
								</div>

							</div>
							<div class="col-6">
								<h4 class="mt-0 mb-6">CDN Data</h4>

								<div class="row form-group">
									<div class="col-3 ">
										ID
									</div>
									<div class="col-9">
										@if(!empty($vfile->cfs_uid))
											<span class="cf-uid">{!!  $vfile->cfs_uid  !!}</span>
										@endif
									</div>
								</div>

								<div class="row form-group mb-10">
									<div class="col-3">
										Offset
									</div>
									<div class="col-9">
										@if($vfile->cfs_ready == 1)
											{!!  $vfile->cfs_thumb_offset  !!} sec
										@endif
									</div>
								</div>

							</div>
						</div>

						@if($vfile->cfs_ready == 1)
							<div class="row">

								<div class="col-12 col-sm-6">
									<div class="mb-10">
										<h4 class="mt-0 mb-6">CDN Video</h4>
										<div class="embed-responsive embed-responsive-16by9">
											<iframe src="{{ $videoUrl }}"
											        class="cf-video-iframe"
											        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
											        allowfullscreen="true"></iframe>
										</div>
									</div>
								</div>

								<div class="col-12 col-sm-6">
									<div class="mb-10">
										<h4 class="mt-0 mb-6">CDN Thumbnail</h4>
										<img src="{{ $thumbnail }}" class="cf-cdn-thumbnail"/>
									</div>
								</div>

							</div>
						@endif

					</div>

				</div>

			</div>

		</div>

		<div class="row py-2 cf-row-divider"></div>

	@endforeach
</div>