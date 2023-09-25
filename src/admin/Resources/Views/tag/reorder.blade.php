@extends('lara-admin::layout2')

@section('head-after')
	<link rel="stylesheet" href="{{ asset('assets/admin/plugins/legacy/nested-sortable/nested-sortable-custom.css') }}">
@endsection

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
	<script src="{{ asset('assets/admin/plugins/legacy/nested-sortable/jquery.mjs.nestedSortable2.js') }}" type="text/javascript"></script>


	<script type="text/javascript">
		jQuery(document).ready(function ($) {

			@if($data->taxonomy->has_hierarchy)

				// initialize the nested sortable plugin
				$('.sortable').nestedSortable({
					forcePlaceholderSize: true,
					handle: 'div',
					helper: 'clone',
					items: 'li',
					opacity: .6,
					placeholder: 'placeholder',
					revert: 250,
					tabSize: 25,
					tolerance: 'pointer',
					toleranceElement: '> div',
					maxLevels: 4,
					protectRoot: true,

					isTree: true,
					expandOnHover: 700,
					startCollapsed: false
				});
			@else
				// initialize the nested sortable plugin
				$('.sortable').nestedSortable({
					forcePlaceholderSize: true,
					handle: 'div',
					helper: 'clone',
					items: 'li',
					opacity: .6,
					placeholder: 'placeholder',
					revert: 250,
					tabSize: 25,
					tolerance: 'pointer',
					toleranceElement: '> div',
					maxLevels: 2,
					protectRoot: true,

					isTree: false,
				});

			@endif

			$('.disclose').on('click', function () {
				$(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
			});

			$('#toArray').click(function (e) {

				e.preventDefault();

				// get the current tree order
				arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});
				hierarchy = $('ol.sortable').nestedSortable('toHierarchy', {startDepthCount: 0});

				// log it
				// console.log(arraied);
				//

				for (key in arraied) {
					console.log(arraied[key]);
				}

				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});

				// send it with POST
				$.ajax({
					url: '{{ route('admin.tag.reorder') }}',
					type: 'POST',
					data: {tree: hierarchy},
				})
					.done(function () {
						console.log("success");
					})
					.fail(function () {
						console.log("error");
					})
					.always(function () {
						console.log("complete");
						location.reload();
					});

			});


		});
	</script>

@endsection
