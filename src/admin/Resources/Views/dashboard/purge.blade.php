@extends('lara-admin::layout')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include('lara-admin::dashboard.purge.pagetitle')
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			{{ html()->form('POST', route('admin.dashboard.purgeprocess'))
						->id('purgeprocess')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

				<div class="content-box main-content">
					<div class="content-box-header">
						@include('lara-admin::dashboard.purge.header')
					</div>
					<div class="content-box-body">

						@include('lara-admin::dashboard.purge.content')

					</div>
				</div>

			{{ html()->form()->close() }}

		</div>
	</div>
	<!--end::Content-->

@endsection

@section('scripts-after')

	<script>

		const swalButton = document.querySelector('button.swal-proces-purge-confirm');
		const form = document.getElementById('purgeprocess');
		if(swalButton) {
			swalButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.loss_of_data') }}',
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

	</script>


@endsection