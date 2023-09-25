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


					{{ html()->form('POST', route('admin.'.$entity->getEntityRouteKey().'.batch'))
						->id('batchform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

					@include($data->partials['content'])

					{{ html()->hidden('language', $clanguage) }}

					{{ html()->form()->close() }}

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

@endsection

@section('scripts-after')

	<script>

		$(document).ready(function () {

			const swalButton = document.querySelector('input.swal-seo-reset-confirm');
			const form = document.getElementById('seoform');
			if(swalButton) {
				swalButton.addEventListener('click', e => {
					e.preventDefault();
					Swal.fire({
						title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
						text: "{{ ucfirst(_lanq('lara-admin::seo.message.data_reset')) }}",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
						cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
					}).then((result) => {
						if (result.isConfirmed) {
							form.submit()
						}
					});
				});
			}

		});

	</script>

@endsection