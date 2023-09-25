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

					{{ html()->form('POST', route('admin.cache.clear'))
						->id('batchform')
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

@section('scripts-after')

	@include('lara-admin::_scripts.batch')

	<script type="text/javascript">

		$(document).ready(function () {

			const button = document.querySelector('input.swal-cache-clear-confirm');

			button.addEventListener('click', e => {

				e.preventDefault();

				const form = document.querySelector('#batchform');

				let checkedCount = $('input:checkbox:checked').length;

				if (checkedCount > 0) {

					Swal.fire({
						title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
						text: "{{ ucfirst(_lanq('lara-admin::cache.message.cache_flush_warning')) }}",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
						cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
					}).then((result) => {
						if (result.isConfirmed) {
							$("#cacheclear")
								.val("{{ ucfirst(_lanq('lara-admin::cache.message.please_wait')) }} ...")
								.attr('disabled', 'disabled');
							form.submit()
						}
					});

				} else {

					alert('no items selected')

				}
			});

		});

	</script>

@endsection