@extends('lara-admin::layout')

@section('content')

	{{ html()->form('POST', route('admin.'.$entity->getEntityRouteKey().'.batch'))
		->id('menuform')
		->attributes(['accept-charset' => 'UTF-8'])
		->open() }}

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include($data->partials['pagetitle'])
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			<div class="content-box main-content">
				<div class="content-box-header">
					@include($data->partials['header'])
				</div>
				<div class="content-box-body">

					@include($data->partials['content'])
					@include('lara-admin::'.$entity->getEntityKey().'.index.modal_edit')

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->


	{{ html()->hidden('language', $clanguage) }}

	{{ html()->form()->close() }}

	@include('lara-admin::'.$entity->getEntityKey().'.index.modal_create')



@endsection

@section('scripts-after')

	<script type="text/javascript">

		$(".open-create-menu-modal").click(function () {
			var parentID = $(this).data('parentid');
			$('#new_parent_id').val(parentID).trigger('change');
		});

		$(".open-menu-modal").click(function () {

			var menuItemId = $(this).data('id');
			var menuStatus = $(this).data('status');
			var menuTitle = $(this).data('title');
			var menuType = $(this).data('type');
			var menuLocked = $(this).data('locked');
			var menuAuth = $(this).data('auth');
			var menuRoute = $(this).data('route');
			var menuSlug = $(this).data('slug');
			var menuView = $(this).data('entview');
			var menuTagID = $(this).data('tagid');
			var menuObjectID = $(this).data('objectid');
			var menuBlockID = $(this).data('blockid');

			if (menuType == 'page') {
				$('#row_menu_page_edit').show();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (menuType == 'parent') {
				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (menuType == 'entity') {

				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').show();
				$('#row_menu_form_view_edit').hide();
				$('#row_menu_tag_edit').hide();

				$('#tag_id').find('option').remove();

				$.ajax({
					url: '/admin/tag/' + menuView + '/gettags/',
					type: 'get',
					dataType: 'json',
					success: function (response) {

						if (response['has_tags']) {

							if (response['data'] != null) {


								var option = '<option value="">[ show all ]</option>';
								$("#tag_id").append(option);

								var tags = response['data'];
								for (var i in tags) {
									var id = tags[i].id;
									var title = tags[i].title;
									var option = "<option value='" + id + "'>" + title + "</option>";
									$("#tag_id").append(option);
								}

								$('#tag_id').val(menuTagID).trigger('change');
							}

							$('#row_menu_tag_edit').show();

						} else {

							$('#row_menu_tag_edit').hide();

						}

					}
				});
			}
			if (menuType == 'form') {
				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').show();
				$('#row_menu_tag_edit').hide();
			}

			$('input[name="menu_item_id"]').val(menuItemId);
			$('input[name="title"]').val(menuTitle);
			$('input[name="route"]').val(menuRoute);
			$('input[name="slug"]').val(menuSlug);
			$('#type').val(menuType).trigger('change');
			$('#publish').val(menuStatus).trigger('change');
			$('#entity_view_id').val(menuView).trigger('change');
			$('#entity_form_view_id').val(menuView).trigger('change');
			$('#object_id').val(menuObjectID).trigger('change');

			if (menuLocked == 1) {
				$('#locked_by_admin').prop('checked', true);
			} else {
				$('#locked_by_admin').prop('checked', false);
			}
			if (menuAuth == 1) {
				$('#route_has_auth').prop('checked', true);
			} else {
				$('#route_has_auth').prop('checked', false);
			}
		});

		function toggleMenuEditOptions(val) {

			if (val == 'page') {
				$('#row_menu_page_edit').show();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (val == 'parent') {
				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (val == 'entity') {
				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').show();
				$('#row_menu_form_view_edit').hide();
				// $('#row_menu_tag_edit').show();
			}
			if (val == 'form') {
				$('#row_menu_page_edit').hide();
				$('#row_menu_view_edit').hide();
				$('#row_menu_form_view_edit').show();
				$('#row_menu_tag_edit').hide();
			}
		}

		function toggleMenuCreateOptions(val) {

			if (val == 'page') {
				$('#row_menu_page_create').show();
				$('#row_menu_view_create').hide();
				$('#row_menu_form_view_create').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (val == 'parent') {
				$('#row_menu_page_create').hide();
				$('#row_menu_view_create').hide();
				$('#row_menu_form_view_create').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (val == 'entity') {
				$('#row_menu_page_create').hide();
				$('#row_menu_view_create').show();
				$('#row_menu_form_view_create').hide();
				$('#row_menu_tag_edit').hide();
			}
			if (val == 'form') {
				$('#row_menu_page_create').hide();
				$('#row_menu_view_create').hide();
				$('#row_menu_form_view_create').show();
				$('#row_menu_tag_edit').hide();
			}

		}

	</script>

@endsection
