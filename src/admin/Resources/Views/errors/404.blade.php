@extends('lara-admin::errors')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include('lara-admin::errors.404.pagetitle')
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			<div class="content-box main-content">
				<div class="content-box-header">
					@include('lara-admin::errors.404.header')
				</div>
				<div class="content-box-body">

					<p>{{ _lanq('lara-admin::default.error.404_text') }}</p>

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

@endsection
