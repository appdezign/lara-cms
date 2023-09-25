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

					{{ html()->form('POST', route('admin.dashboard.getrefresh'))
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

	<script type="text/javascript">

		$(document).ready(function () {
			$("#refresh").click(function () {
				$("#refresh")
					.val("{{ ucfirst(_lanq('lara-admin::cache.message.please_wait')) }} ...")
					.attr('disabled', 'disabled');
			});
		});

	</script>

	<script>

		$(document).ready(function () {

			// check all (JS)
			let checkAll = document.querySelector('input.js-check-all');
			let checkboxes = document.querySelectorAll('input.js-check');

			checkAll.addEventListener('change', (event) => {
				if (event.currentTarget.checked) {
					checkboxes.forEach((chbx) => {
						chbx.checked = true;
					});
				} else {
					checkboxes.forEach((chbx) => {
						chbx.checked = false;
					});
				}
			});

			checkboxes.forEach((item) => {
				item.addEventListener('change', function (event) {
					let checkedCount = 0;
					checkboxes.forEach((chbx) => {
						if (chbx.checked) {
							checkedCount++;
						}
					});
					if (checkedCount < checkboxes.length) {
						checkAll.checked = false;
					} else {
						checkAll.checked = true;
					}
				});
			});

		});

	</script>

	<script type="text/javascript">

		$(document).ready(function () {

			const button = document.querySelector('input.swal-ga-refresh-confirm');

			button.addEventListener('click', e => {

				e.preventDefault();

				const form = document.querySelector('#batchform');

				let checkedCount = $('input:checkbox:checked').length;

				if (checkedCount > 0) {

					Swal.fire({
						title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
						text: "{{ ucfirst(_lanq('lara-admin::ga.message.ga_timeout_warning')) }}",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
						cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
					}).then(function () {
						$("#refresh")
							.val("{{ ucfirst(_lanq('lara-admin::cache.message.please_wait')) }} ...")
							.attr('disabled', 'disabled');

						form.submit()
					}, function (dismiss) {
						if (dismiss == 'cancel') {
							$("#refresh")
								.val("{{ _lanq('lara-admin::ga.button.refresh') }}")
								.attr('disabled', false);
						}
					}).catch(swal.noop)

				} else {

					form.submit();

				}
			});

		});

	</script>

@endsection