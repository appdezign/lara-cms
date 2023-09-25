@extends('lara-admin::layout2')

@section('content')

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

@endsection


@section('scripts-after')

	<script src="{{ asset('assets/admin/plugins/legacy/nested-sortable/jquery-ui.min.js') }}" type="text/javascript"></script>

	<script type="text/javascript">

		var changePosition = function (requestData) {

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				'url': '/admin/sort',
				'type': 'POST',
				'data': requestData,
				'success': function (data) {
					if (data.success) {
						console.log('Saved!');
					} else {
						console.error(data.errors);
					}
				},
				'error': function () {
					console.error('Something wrong!');
				}
			});
		};

		$(document).ready(function () {
			var $sortableTable = $('.sortable');
			if ($sortableTable.length > 0) {
				$sortableTable.sortable({
					handle: '.sortable-handle',
					axis: 'y',
					update: function (a, b) {

						var entityName = $(this).data('entityname');
						var $sorted = b.item;

						var $previous = $sorted.prev();
						var $next = $sorted.next();

						if ($previous.length > 0) {
							changePosition({
								parentId: $sorted.data('parentid'),
								type: 'moveAfter',
								entityName: entityName,
								id: $sorted.data('itemid'),
								positionEntityId: $previous.data('itemid')
							});
						} else if ($next.length > 0) {
							changePosition({
								parentId: $sorted.data('parentid'),
								type: 'moveBefore',
								entityName: entityName,
								id: $sorted.data('itemid'),
								positionEntityId: $next.data('itemid')
							});
						} else {
							console.error('Something wrong!');
						}
					},
					cursor: "move"
				});
			}

			$('.sortable td').each(function () {
				$(this).css('width', $(this).width() + 'px');
			});

		});

	</script>

@endsection
