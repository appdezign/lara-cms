<script type="text/javascript">

	$(document).ready(function () {

		Dropzone.autoDiscover = false;

		@if($entity->hasImages() && $data->object->media->count() < $entity->getMaxImages())

		// DropZone for Images
		var DropzoneImages = new Dropzone("#dropzone-images", {
			url: "{{ route('admin.upload', 'image') }}",
			paramName: "fileuploads",
			acceptedFiles: "{{ implode (", ", config('lara.upload_allowed_images')) }}",
			maxFilesize: {{ config('lara.upload_maxsize_image') * 1024 * 1024 }},
			parallelUploads: 20,
			resizeWidth: 3840,
			resizeHeight: 2560,
			resizeQuality: 1.0,
			previewsContainer: "#dz-preview-zone1",
			clickable: "#dz-preview-zone1",
			init: function() {

				this.on("error", function(file, response) {
					alert(response);
					DropzoneImages.removeAllFiles(true);
				});

				this.on("sending", function (file, xhr, data) {
					data.append("_token", "{{ csrf_token() }}");
					data.append("object_id", "{{ $data->object->id }}");
					data.append("entity_key", "{{ $entity->getEntityKey() }}");
					data.append("dz_session_id", "{{ Date::parse(\Carbon\Carbon::now())->format('YmdHis') }}");
				});

				this.on("queuecomplete", function (file) {
					setTimeout(function () {
						$("#uploadImages").fadeOut("fast", function () {
							// Animation complete.
						});
						$("#saveImagesButton").fadeIn("fast", function () {
							// Animation complete.
						});
						$("#saveImagesButtonMobile").fadeIn("fast", function () {
							// Animation complete.
						});
						$("#cancelImagesButton").fadeIn("fast", function () {
							// Animation complete.
						});

					}, 1000);
				});
			},
		});

		@endif

		@if($entity->hasFiles() && $data->object->files->count() < $entity->getMaxFiles())

		// DropZone for Files
		var DropzoneFiles = new Dropzone("#dropzone-files", {
			url: "{{ route('admin.upload', 'file') }}",
			paramName: "fileuploads",
			acceptedFiles: "{{ implode (", ", config('lara.upload_allowed_files')) }}",
			maxFilesize: {{ config('lara.upload_maxsize_file') * 1024 * 1024 }},
			parallelUploads: 1,
			previewsContainer: "#dz-preview-zone2",
			clickable: "#dz-preview-zone2",
			init: function() {

				this.on("error", function(file, response) {
					alert(response);
					DropzoneFiles.removeAllFiles(true);
				});

				this.on("sending", function (file, xhr, data) {
					data.append("_token", "{{ csrf_token() }}");
					data.append("object_id", "{{ $data->object->id }}");
					data.append("entity_key", "{{ $entity->getEntityKey() }}");
					data.append("dz_session_id", "{{ Date::parse(\Carbon\Carbon::now())->format('YmdHis') }}");
				});

				this.on("success", function(file, response) {
					this.on("queuecomplete", function (file) {
						setTimeout(function () {
							$("#uploadFiles").fadeOut("fast", function () {
								// Animation complete.
							});
							$("#saveFilesButton").fadeIn("fast", function () {
								// Animation complete.
							});
							$("#saveFilesButtonMobile").fadeIn("fast", function () {
								// Animation complete.
							});
							$("#cancelFilesButton").fadeIn("fast", function () {
								// Animation complete.
							});

						}, 1000);
					});
				});
			},
		});
		@endif

		@if($entity->hasVideoFiles() && $data->object->videofiles->count() < $entity->getMaxVideoFiles() && $data->object->videos()->count() == 0)

		// DropZone for Video Files
		var DropzoneVideoFiles = new Dropzone("#dropzone-videofiles", {
			url: "{{ route('admin.upload', 'videofile') }}",
			paramName: "fileuploads",
			acceptedFiles: "{{ implode (", ", config('lara.upload_allowed_videofiles')) }}",
			maxFilesize: {{ config('lara.upload_maxsize_videofile') * 1024 * 1024 }},
			parallelUploads: 1,
			previewsContainer: "#dz-preview-zone3",
			clickable: "#dz-preview-zone3",
			init: function() {

				this.on("error", function(file, response) {
					alert(response);
					DropzoneVideoFiles.removeAllFiles(true);
				});

				this.on("sending", function (file, xhr, data) {
					data.append("_token", "{{ csrf_token() }}");
					data.append("object_id", "{{ $data->object->id }}");
					data.append("entity_key", "{{ $entity->getEntityKey() }}");
					data.append("dz_session_id", "{{ Date::parse(\Carbon\Carbon::now())->format('YmdHis') }}");
				});

				this.on("success", function(file, response) {

					this.on("queuecomplete", function (file) {

						setTimeout(function () {
							$("#uploadVideoFiles").fadeOut("fast", function () {
								// Animation complete.
							});
							$("#saveVideoFilesButton").fadeIn("fast", function () {
								// Animation complete.
							});
							$("#saveVideoFilesButtonMobile").fadeIn("fast", function () {
								// Animation complete.
							});
							$("#cancelVideoFilesButton").fadeIn("fast", function () {
								// Animation complete.
							});

						}, 1000);
					});
				});
			},
		});

		@endif

	});

</script>