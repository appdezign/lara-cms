@extends('lara-admin::layout')

@section('content')


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

					{{ html()->form('POST', route('admin.language.purgeprocess'))
						->id('contentform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

					@include($data->partials['content'])

					{{ html()->form()->close() }}

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

@endsection

