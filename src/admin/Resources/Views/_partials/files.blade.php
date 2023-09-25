<div class="box box-default collapsed-box">

	<div class="box-header with-border">

		<div class="box-tools box-tools-files">

			<button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse"
			        data-bs-target="#kt_files_collapsible_dropzone">
				<i class="fas fa-upload"></i> Upload
			</button>

			@if($data->object->files->count() < $entity->getMaxFiles())

				<div class="d-none d-md-block float-end">
					{{ html()->input('submit', '_save_file_upload', _lanq('lara-admin::default.button.save_files'))->id('saveFilesButton')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				<div class="d-block d-md-none float-end">
					{{ html()->input('submit', '_save_file_upload_mobile', _lanq('lara-admin::default.button.save'))->id('saveFilesButtonMobile')->class('btn btn-sm btn-danger')->style(['display' => 'none']) }}
				</div>
				{{ html()->input('submit', '_cancel_file_upload', _lanq('lara-admin::default.button.cancel'))->id('cancelFilesButton')->class('btn btn-sm btn-outline btn-outline-success float-end')->style(['display' => 'none', 'margin-right' => '20px']) }}
			@endif
		</div>
	</div>

	<div id="kt_files_collapsible_dropzone" class="collapse">
		@if($data->object->files->count() < $entity->getMaxFiles())
			<div id="dropzone-files" class="box-body lara-dropzone">

				<div class="row mb-3">
					<div class="col-6 text-start">
						<span class="text-muted">format: {{ parseFileFormats(config('lara.upload_allowed_files')) }}</span>
					</div>
					<div class="col-6 text-end">
						<span class="text-muted">max: {{ config('lara.upload_maxsize_file') }} MB</span>
					</div>
				</div>

				<div class="dropzone2 needsclick dz-clickable">
					<div id="dz-preview-zone2" class="dropzone-previews">
						<div class="dz-message needsclick">
							{{ _lanq('lara-admin::default.button.dropfileshere') }}<br>
						</div>
					</div>
				</div>

			</div>
		@else
			<div id="dropzone-files" class="box-body lara-dropzone">
				{{ _lanq('lara-admin::default.message.max_files_reached') }}
			</div>
		@endif
	</div>
</div>


@if($data->object->files->count() && $data->object->publish == 0)
	<div class="row">
		<div class="col-12 mb-3 p-3">
			<div class="alert alert-warning alert-important text-center">
				{{ _lanq('lara-admin::default.message.docs_not_published_yet') }}
			</div>
		</div>
	</div>
@endif

<div class="lara-file-list">
	@foreach( $data->object->files as $doc )
		<div class="row lara-file-list-item py-3">
			<div class="col-12 col-sm-2 py-3 text-center file-types">
				@if($doc->mimetype == 'application/pdf')
					<i class="fal fa-file-pdf"></i>
				@else
					<i class="fal fa-file-alt"></i>
				@endif
			</div>

			<div class="col-6 action-icons d-block d-sm-none">
				<a href="javascript:void(0)" class="float-end" onclick="fileDataToggler({{ $doc->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-6 action-icons d-block d-sm-none">
				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_file')->value('_delete_file_'.$doc->id)->class('btn btn-link') }}
			</div>

			<div class="col-12 col-sm-8">
				{{-- FILE INFO PANEL--}}
				<div id="file_info_{{ $doc->id }}" class="file-info-panel">

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ _lanq('lara-admin::default.column.filename') }}
						</div>
						<div class="col-12 col-md-9 col-lg-9">
							@if($data->object->publish == 1)
								<a href="{{ $entity->getUrlForFiles() }}/{{ $doc->filename }}"
								   target="_blank"
								   style="display:block;">
									{{ $doc->filename }}
								</a>
							@else
								<a href="{{ $entity->getUrlForFiles() }}/_archive/{{ $doc->filename }}"
								   target="_blank"
								   style="display:block;">
									{{ $doc->filename }}
								</a>
							@endif
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3 text-muted">
							{{ _lanq('lara-admin::default.column.doctitle') }}
						</div>
						<div class="col-12 col-md-9 col-lg-9 text-muted">
							{!!  $doc->title  !!}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3 text-muted">
							{{ _lanq('lara-admin::default.column.docdate') }}
						</div>
						<div class="col-12 col-md-9 col-lg-9 text-muted">
							{!!  $doc->docdate  !!}
						</div>
					</div>

				</div>

				{{-- FILE EDIT PANEL--}}
				<div id="file_edit_{{ $doc->id }}" class="file-edit-panel" style="display:none">

					<div class="row form-group">
						<div class="col">
							{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit', '_save_file')->value('_save_file_'.$doc->id)->class('btn btn-sm btn-danger float-end') }}
							<a href="javascript:void(0)" class="btn btn-sm btn-outline btn-outline-success float-end me-3"
							   onclick="fileDataToggler({{ $doc->id }});">
								{{ _lanq('lara-admin::default.button.cancel') }}
							</a>

						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ _lanq('lara-admin::default.column.filename') }}
						</div>
						<div class="col-12 col-md-9 col-lg-9">

							@if($data->object->publish == 1)
								<a href="{{ $entity->getUrlForFiles() }}/{{ $doc->filename }}"
								   target="_blank"
								   style="display:block;">
									{{ $doc->filename }}
								</a>
							@else
								<a href="{{ $entity->getUrlForFiles() }}/_archive/{{ $doc->filename }}"
								   target="_blank"
								   style="display:block;">
									{{ $doc->filename }}
								</a>
							@endif

						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label(_lanq('lara-admin::default.column.doctitle').':', '_doctitle_' . $doc->id) }}
						</div>
						<div class="col-12 col-md-9">
							{{ html()->text('_doctitle_' . $doc->id, $doc->title)->class('form-control') }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-12 col-md-3">
							{{ html()->label(_lanq('lara-admin::default.column.docdate').':', '_docdate_' . $doc->id) }}
						</div>
						<div class="col-12 col-md-9">

							<div id="dtp-docdate-{{ $doc->id }}" class="date-flat-pickr">
								{{ html()->text('_docdate_' . $doc->id, $doc->docdate)->class('form-control')->data('input') }}
								<a class="flat-pickr-button" title="toggle" data-toggle>
									<i class="fal fa-calendar-alt"></i>
								</a>
							</div>

						</div>
					</div>



				</div>
			</div>

			<div class="col-1 text-center action-icons d-none d-sm-block">
				<a href="javascript:void(0)" onclick="fileDataToggler({{ $doc->id }});">
					<i class="las la-edit"></i>
				</a>
			</div>
			<div class="col-1 text-center action-icons d-none d-sm-block">
				{{ html()->button('<i class="las la-trash"></i>', 'submit', '_delete_file')->value('_delete_file_'.$doc->id)->class('btn btn-link') }}
			</div>
		</div>
	@endforeach
</div>

@section('scripts-after')

	@parent

	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function () {
			@foreach( $data->object->files as $doc )
			flatpickr("#dtp-docdate-{{ $doc->id }}", {
				dateFormat: "Y-m-d",
				enableTime: false,
				wrap: true,
			});
			@endforeach
		});
	</script>

@endsection

