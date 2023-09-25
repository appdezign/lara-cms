@extends('lara-admin::layout')

@section('content')

	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.user.saveprofile'))
			->id('lara-default-edit-form')
			->attributes(['accept-charset' => 'UTF-8'])
			->class('needs-validation')
			->novalidate()
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

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

	{{ html()->closeModelForm() }}

@endsection

