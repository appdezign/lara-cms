@extends('lara-admin::layout')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include('lara-admin::dashboard.dbcheck.pagetitle')
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			<div class="content-box main-content">
				<div class="content-box-header">
					@include('lara-admin::dashboard.dbcheck.header')
				</div>
				<div class="content-box-body">

					{{ html()->form('POST', route('admin.dashboard.dbcheck'))
						->id('dbform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

					@include('lara-admin::dashboard.dbcheck.content')

					{{ html()->form()->close() }}

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

@endsection