<div class="box box-default collapsed-box">

	<div class="box-header with-border">

		<div class="box-tools box-tools-images">

			<button class="btn btn-sm btn-info" type="button"
			        data-bs-toggle="collapse"
			        data-bs-target="#kt_images_collapsible_dropzone">
				<i class="fas fa-upload"></i> Upload
			</button>

			@if($data->object->media->count() < $entity->getMaxImages())

				<div class="d-none d-md-block float-end">
					{{ html()->input('submit', '_save_image_upload', _lanq('lara-admin::default.button.save_images'))->id('saveImagesButton')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				<div class="d-block d-md-none float-end">
					{{ html()->input('submit', '_save_image_upload_mobile', _lanq('lara-admin::default.button.save'))->id('saveImagesButtonMobile')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				{{ html()->input('submit', '_cancel_image_upload', _lanq('lara-admin::default.button.cancel_images'))->id('cancelImagesButton')->class('btn btn-sm btn-outline btn-outline-success float-end')->style(['display' => 'none', 'margin-right' => '20px']) }}
			@endif

		</div>
	</div>

	<div id="kt_images_collapsible_dropzone" class="collapse">
		@if($data->object->media->count() < $entity->getMaxImages())
			<div id="dropzone-images" class="box-body lara-dropzone">

				<div class="row mb-3">
					<div class="col-6 text-start">
						<span class="text-muted">format: {{ parseFileFormats(config('lara.upload_allowed_images')) }}</span>
					</div>
					<div class="col-6 text-end">
						<span class="text-muted">max: {{ config('lara.upload_maxsize_image') }} MB</span>
					</div>
				</div>

				<div class="dropzone2 needsclick dz-clickable">
					<div id="dz-preview-zone1" class="dropzone-previews">
						<div class="dz-message needsclick">
							{{ _lanq('lara-admin::default.button.dropfileshere') }}<br>
						</div>
					</div>
				</div>

			</div>
		@else
			<div id="dropzone-images" class="box-body lara-dropzone text-center">
				{{ _lanq('lara-admin::default.message.max_images_reached') }}
			</div>
		@endif
	</div>
</div>

<div class="lara-image-list">

	<?php
	$isFeatured = false;
	$isHero = false;
	$isGallery = false;
	$galleryIsFirstRow = false;
	$galleryFirstRowFound = false;
	?>

	@foreach( $data->object->media as $image )
			<?php
			if ($image->featured && $image->ishero) {
				$isFeatured = true;
				$isHero = true;
				$rowHeader = 'Featured &amp; Hero';
			} elseif ($image->featured == 1) {
				$isFeatured = true;
				$rowHeader = 'Featured';
			} elseif ($image->ishero == 1) {
				$isHero = true;
				$rowHeader = 'Hero';
			} else {
				$isGallery = true;
				if (!$galleryFirstRowFound) {
					$rowHeader = 'Gallery';
					$galleryIsFirstRow = true;
					$galleryFirstRowFound = true;
				} else {
					$galleryIsFirstRow = false;
					$rowHeader = null;
				}
			}
			?>
		@if($rowHeader)
			@if (!$loop->first)
				<div class="row">
					<div class="col">
						&nbsp;
					</div>
				</div>
			@endif
			<div class="row image-row-header">
				<div class="col-12 action-icons">
					@if($isGallery && $galleryIsFirstRow)
						<a href="{{ route('admin.image.reorder', ['type' => $entity->getEntityRouteKey(), 'id' => $data->object->id] ) }}"
						   class="btn btn-sm btn-icon btn-outline btn-outline-primary float-end"
						   title="{{ _lanq('lara-admin::default.button.reorder') }}">
							<i class="far fa-arrows"></i>
						</a>
					@endif
					<h4>{!!  $rowHeader !!}</h4>
				</div>
			</div>
		@endif

		<div class="row py-3 @if($isGallery) image-row-border-bottom @endif">
			<div class="col-12 col-sm-4 text-center">
				{{-- Img Thumbnail--}}
				<div @if($image->featured || $image->ishero) class="lara-object-image-featured"
				     @else class="lara-object-image" @endif>
					@if($image->prevent_cropping == 1)
						<img src="{{ route('imgcache', ['width' => 200, 'height' => 0, 'fit' => 2, 'fitpos' => 'center', 'quality' => 90, 'filename' => $image->filename]) }}"
						     width="200"/>
					@else
						<img src="{{ route('imgcache', ['width' => 200, 'height' => 200, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $image->filename]) }}"
						     width="200"/>
					@endif
				</div>
			</div>

			<div class="col-6 action-icons d-block d-sm-none">
				<a href="javascript:void(0)" class="float-end" onclick="imageDataToggler({{ $image->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-6 action-icons d-block d-sm-none">

				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_image')->value('_delete_image_'.$image->id)->class('btn btn-link') }}

			</div>

			<div class="col-12 col-sm-6">

				{{-- IMAGE INFO PANEL--}}
				<div id="image_info_{{ $image->id }}" class="image-info-panel">

					<div class="row form-group">
						<div class="col-4">
							caption:
						</div>
						<div class="col-8">
							{!!  $image->caption  !!}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-4">
							image alt:
						</div>
						<div class="col-8">
							{{ $image->image_alt }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-4">
							image title
						</div>
						<div class="col-8">
							{{ $image->image_title }}
						</div>
					</div>

					<div class="image-tags">
						@if($image->featured == 1)
							<div class="image-tag">featured</div>
						@endif
						@if($image->ishero == 1)
							<div class="image-tag">
								<?php $heroSizes = config('lara-admin.hero_sizes'); ?>
								hero {{ $heroSizes[$image->herosize] }}
							</div>
						@endif
						@if($data->object->opengraph && $image->filename == $data->object->opengraph->og_image)
							<div class="image-tag">opengraph</div>
						@endif

						@if($data->object->hasGallery() && ($image->featured || $image->ishero))
							@if($image->hide_in_gallery == 1)
								<img src="{{ asset('assets/admin/img/icon-gallery-grey.png') }}" width="24"
								     alt="hide in gallery"
								     title="hide in gallery"
								     class="float-end"/>
							@else
								<img src="{{ asset('assets/admin/img/icon-gallery.png') }}" width="24"
								     alt="show in gallery"
								     title="show in gallery"
								     class="float-end"/>
							@endif
						@endif
					</div>

				</div>

				{{-- IMAGE EDIT PANEL --}}
				<div id="image_edit_{{ $image->id }}" class="image-edit-panel" style="display:none;">

					<div class="row form-group">

						<div class="col">

							{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit', '_save_image')->value('_save_image_'.$image->id)->class('btn btn-sm btn-danger float-end') }}

							<a href="javascript:void(0)" class="btn btn-sm btn-outline btn-outline-success float-end me-3"
							   onclick="imageDataToggler({{ $image->id }});">
								{{ _lanq('lara-admin::default.button.cancel') }}
							</a>

						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('featured:', '_image_featured') }}
						</div>
						<div class="col-12 col-md-9">

							<div class="form-check">
								@if($image->featured == 1)
									{{ html()->hidden('_image_featured_' . $image->id, 1) }}
									{{ html()->checkbox('_image_featured_' . $image->id, old('_image_featured_' . $image->id, $image->featured), 1)->class('form-check-input')->disabled() }}
								@else
									{{ html()->hidden('_image_featured_' . $image->id, 0) }}
									{{ html()->checkbox('_image_featured_' . $image->id, old('_image_featured_' . $image->id, $image->featured), 1)->class('form-check-input') }}
								@endif
							</div>
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('hero:', '_image_ishero') }}
						</div>
						<div class="col-12 col-md-9">

							<div class="form-check">
								{{ html()->hidden('_image_ishero_' . $image->id, 0) }}
								{{ html()->checkbox('_image_ishero_' . $image->id, old('_image_ishero_' . $image->id, $image->ishero), 1)->class('form-check-input') }}
							</div>
						</div>
					</div>

					@if($image->ishero == 0)
						<div class="row form-group">
							<div class="col-12 col-md-3">
								{{ html()->label(_lanq('lara-admin::default.label.prevent_cropping') . ':', '_prevent_cropping') }}
							</div>
							<div class="col-12 col-md-9">
								<div class="form-check">
									{{ html()->hidden('_prevent_cropping_' . $image->id, 0) }}
									{{ html()->checkbox('_prevent_cropping_' . $image->id, old('_prevent_cropping_' . $image->id, $image->prevent_cropping), 1)->class('form-check-input') }}
								</div>
							</div>
						</div>
					@else
						{{ html()->hidden('_prevent_cropping_' . $image->id, $image->prevent_cropping) }}
					@endif

					@if($image->ishero == 1)
						<div class="row form-group">
							<div class="col-12 col-md-3">
								{{ html()->label('hero size:', '_image_herosize') }}
							</div>
							<div class="col-12 col-md-9">
								{{ html()->select('_herosize_' . $image->id, config('lara-admin.hero_sizes'), old('_herosize_' . $image->id, $image->herosize))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}

							</div>
						</div>
					@else
						{{ html()->hidden('_herosize_' . $image->id, $image->herosize) }}
					@endif

					@if($data->object->hasGallery())
						@if($image->featured == 1 || $image->ishero)
							<div class="row form-group">
								<div class="col-12 col-md-3">
									{{ html()->label('hide in gallery:', '_hide_in_gallery') }}
								</div>
								<div class="col-12 col-md-9">
									<div class="form-check">
										{{ html()->hidden('_hide_in_gallery_' . $image->id, 0) }}
										{{ html()->checkbox('_hide_in_gallery_' . $image->id, old('_hide_in_gallery_' . $image->id, $image->hide_in_gallery), 1)->class('form-check-input') }}
									</div>
								</div>
							</div>
						@else
							{{ html()->hidden('_hide_in_gallery_' . $image->id, 0) }}
						@endif
					@else
						{{ html()->hidden('_hide_in_gallery_' . $image->id, $image->hide_in_gallery) }}
					@endif

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('caption:', '_caption_' . $image->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->textarea('_caption_' . $image->id, $image->caption)->class('form-control  tinymin')->rows(4) }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('image alt:', '_image_alt_' . $image->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->text('_image_alt_' . $image->id, $image->image_alt)->class('form-control') }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label('image title:', '_image_title_' . $image->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->text('_image_title_' . $image->id, $image->image_title)->class('form-control') }}
						</div>
					</div>
				</div>

			</div>
			<div class="col-1 text-center action-icons d-none d-sm-block">
				<a href="javascript:void(0)" onclick="imageDataToggler({{ $image->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-1 text-center action-icons d-none d-sm-block">
				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_image')->value('_delete_image_'.$image->id)->class('btn btn-link') }}
			</div>

		</div>
	@endforeach
</div>

