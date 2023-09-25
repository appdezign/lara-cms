@if($data->object->videos->count() < $entity->getMaxVideos())

	<div class="box box-default">
		<div class="box-header with-border">
			<h3 class="box-title">{{ _lanq('lara-admin::default.button.video_add') }}</h3>
		</div>
		<!-- /.box-header -->
		<div class="box-body">

			@if($data->object->videofiles()->count())
				{{ _lanq('lara-admin::default.message.object_has_local_video') }}
			@else

				<div class="row form-group">
					<div class="col-3">
						{{ html()->label(_lanq('lara-admin::default.column.video_title') . ':', '_title') }}
					</div>
					<div class="col-9">
						{{ html()->text('_title', null)->class('form-control') }}
					</div>
				</div>
				<div class="row form-group">
					<div class="col-3">
						{{ html()->label(_lanq('lara-admin::default.column.video_code') . ':', '_youtubecode') }}
					</div>
					<div class="col-9">
						{{ html()->text('_youtubecode', null)->class('form-control') }}
					</div>
				</div>
				<div class="row form-group">
					<div class="col">
						{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit', '_add_video')->value('_add_video')->class('btn btn-danger float-end') }}
					</div>
				</div>
			@endif

		</div>

	</div>


@else

	<div class="box box-default">
		<div class="box-header with-border">
			<h3 class="box-title">{{ _lanq('lara-admin::default.button.video_add') }}</h3>
		</div>
		<div class="box-body text-center">
			{{ _lanq('lara-admin::default.message.max_videos_reached') }}<br>
		</div>
	</div>

@endif

<div class="lara-file-list">
	@foreach( $data->object->videos as $video )
		<div class="row py-3 video-row-border-bottom">
			<div class="col-12 col-sm-4 py-3 text-center">
				<div @if($video->featured == 1) class="lara-object-image-featured"
				     @else class="lara-object-image" @endif>

					<div class="ratio ratio-16x9 mb-6">
						<iframe width="560" height="315"
						        src="https://www.youtube.com/embed/{{ $video->youtubecode }}?rel=0" frameborder="0"
						        allow="autoplay; encrypted-media" allowfullscreen></iframe>
					</div>

				</div>
			</div>

			<div class="col-4 text-center action-icons d-block d-sm-none">
				<a href="javascript:void(0)" onclick="videoDataToggler({{ $video->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-4 text-center action-icons d-block d-sm-none">
				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_video')->value('_delete_video_'.$video->id)->class('btn btn-link') }}
			</div>

			<div class="col-12 col-sm-6">
				{{-- FILE INFO PANEL--}}
				<div id="video_info_{{ $video->id }}" class="video-info-panel">

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ _lanq('lara-admin::default.column.video_title') }}:
						</div>
						<div class="col-12 col-md-9 col-lg-9">
							{{ $video->title }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ _lanq('lara-admin::default.column.video_code') }}:
						</div>
						<div class="col-12 col-md-9 col-lg-9">
							{{ $video->youtubecode }}
						</div>
					</div>

					<div class="image-tags">
						@if($video->featured == 1)
							<div class="image-tag">featured</div>
						@endif
					</div>

				</div>

				{{-- FILE EDIT PANEL--}}
				<div id="video_edit_{{ $video->id }}" class="video-edit-panel" style="display:none">

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('featured:', '_video_featured') }}
						</div>
						<div class="col-12 col-md-9">
							<div class="form-check">
								@if($video->featured == 1)
									{{ html()->hidden('_video_featured', 1) }}
									{{ html()->checkbox('_video_featured_' . $video->id, old('_video_featured_' . $video->id, $video->featured), 1)->class('form-check-input')->disabled() }}
								@else
									{{ html()->hidden('_video_featured', 0) }}
									{{ html()->checkbox('_video_featured_' . $video->id, old('_video_featured_' . $video->id, $video->featured), 1)->class('form-check-input') }}
								@endif
							</div>
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label(_lanq('lara-admin::default.column.video_title').':', '_title_' . $video->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->text('_title_' . $video->id, $video->title)->class('form-control') }}
						</div>
					</div>
					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('youtube code:', '_youtubecode_' . $video->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->text('_youtubecode_' . $video->id, $video->youtubecode)->class('form-control') }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							&nbsp;
						</div>
						<div class="col-12 col-md-9">
							{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit', '_save_video')->value('_save_video_'.$video->id)->class('btn btn-danger') }}
						</div>
					</div>

				</div>
			</div>

			<div class="col-1 text-center action-icons d-none d-sm-block">
				<a href="javascript:void(0)" onclick="videoDataToggler({{ $video->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-1 text-center action-icons d-none d-sm-block">
				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_video')->value('_delete_video_'.$video->id)->class('btn btn-link') }}
			</div>
		</div>
	@endforeach
</div>

