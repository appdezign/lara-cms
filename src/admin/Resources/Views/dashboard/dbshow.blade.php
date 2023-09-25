@extends('lara-admin::layout')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include('lara-admin::dashboard.dbshow.pagetitle')
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			{{ html()->form('POST', route('admin.dashboard.dbcheck'))
						->id('dbform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

				<div class="content-box main-content">
					<div class="content-box-header">
						@include('lara-admin::dashboard.dbshow.header')
					</div>
					<div class="content-box-body">

						@include('lara-admin::dashboard.dbshow.content')

					</div>
				</div>

			{{ html()->form()->close() }}

		</div>
	</div>
	<!--end::Content-->

@endsection