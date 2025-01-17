@extends('lara-common::layout-auth')

@section('content')

	<!--begin::Card-->
	<div class="card rounded-2 w-md-400px">
		<!--begin::Card body-->
		<div class="card-body p-10 p-lg-15">

			@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@else
				@include('flash::message')
			@endif

			<!--begin::Form-->
			<form role="form" method="POST" action="{{ route('2fa.verify') }}">

				{!! csrf_field()  !!}

				<div class="text-center mb-12">
					<img src="{{ asset('assets/admin/img/lara8-logo.svg') }}" width="48" alt="Lara" class="mb-4"/>
					<h1 class="fs-2 m-0">Two Factor Authentication</h1>
				</div>

				<div class="fv-row mb-8">
					<input id="one_time_password" type="text" class="form-control" name="one_time_password" value="" placeholder="2FA code" required  autofocus>
				</div>

				<!--begin::Submit button-->
				<div class="d-grid">
					<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
						<span class="indicator-label">Verify</span>
						<span class="indicator-progress">
											<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
										</span>
					</button>
				</div>
				<!--end::Submit button-->

				@if(config('lara.auth.can_reset_password'))
					<hr class="mt-8 mb-6">
					<div class="row">
						<div class="col-sm-12">
							<a href="{{ route('password.request') }}">
								{{ _lanq('lara-common::auth.button.forgot_password') }}
							</a>
						</div>
					</div>
				@endif

			</form>
			<!--end::Form-->
		</div>
		<!--end::Card body-->
	</div>
	<!--end::Card-->

@endsection

@section('scripts-after')

	<script type="text/javascript">
		$(document).ready(function () {
			submitButton = document.querySelector('#kt_sign_in_submit');
			submitButton.addEventListener('click', function (e) {
				submitButton.setAttribute('data-kt-indicator', 'on');
			});
		});
	</script>

@endsection
