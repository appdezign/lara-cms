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


			{{ html()->form('POST', route('admin.translation.saveexport'))
						->id('batchform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

			<div class="content-box main-content">
				<div class="content-box-header">
					@include($data->partials['header'])
				</div>
				<div class="content-box-body">

					@include($data->partials['content'])

				</div>
			</div>

			{{ html()->form()->close() }}

		</div>
	</div>
	<!--end::Content-->


@endsection

@section('scripts-after')

	@include('lara-admin::_scripts.batch')

	<script type="text/javascript">

		$(document).ready(function () {

			const button = document.querySelector('input.swal-translation-export-confirm');

			button.addEventListener('click', e => {

				e.preventDefault();

				const form = document.querySelector('#batchform');

				let checkedCount = $('input:checkbox:checked').length;

				console.log(checkedCount);

				if (checkedCount > 0) {

					Swal.fire({
						title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
						text: "{{ ucfirst(_lanq('lara-admin::translation.message.overwrite_files')) }}",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
						cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
					}).then((result) => {
						if (result.isConfirmed) {
							$("#export")
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