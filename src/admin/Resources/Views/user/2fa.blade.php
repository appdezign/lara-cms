@extends('lara-admin::layout')

@section('content')

	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.user.save2fa'))
			->id('two-factor-activate-form')
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

@section('scripts-after')

	<script>
		const twoFactorActivate = document.querySelector('#_activate_2fa');
		twoFactorActivate.addEventListener('click', e => {
			e.preventDefault();
			Swal.fire({
				title: "{{ ucfirst(_lanq('lara-admin::2fa.message.are_you_sure_title')) }}",
				text: "{{ ucfirst(_lanq('lara-admin::2fa.message.are_you_sure_text')) }}",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
				cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
			}).then((result) => {
				if (result.isConfirmed) {
					$('#two-factor-activate-form').append("<input type='hidden' name='_activate_2fa' value='true' />");
					$('#two-factor-activate-form').submit();
				}
			});
		});
	</script>

@endsection