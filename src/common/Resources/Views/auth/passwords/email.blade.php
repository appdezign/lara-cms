@extends('lara-common::layout-auth')

@section('content')

	<!--begin::Card-->
	<div class="card rounded-2 w-md-400px">

		<div class="card-header d-flex flex-column pt-6 pb-6 justify-content-center align-items-center">
			<img src="{{ asset('assets/admin/img/lara8-logo.svg') }}" width="48" alt="Lara" class="mb-6"/>
			<h1 class="fs-2">
				{{ ucfirst(_lanq('lara-common::auth.passwordforgot.password_forgot_title')) }}
			</h1>
		</div>

		<!--begin::Card body-->
		<div class="card-body px-12 pt-6 pb-12">

			@if ($errors->any())
				<div class="alert alert-danger mb-6">
					<ul class="">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			@if (session('status'))
				<div class="alert alert-success alert-important mb-6" role="alert">
					{{ session('status') }}
				</div>
			@endif

			<!--begin::Form-->
			<form role="form" method="POST" action="{{ route('password.email') }}">

				{!! csrf_field()  !!}

				<div class="fv-row mb-8">
					<!--begin::Email-->
					<input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}"
					       placeholder="{{ _lanq('lara-common::auth.passwordforgot.placeholder_email') }}" required
					       autofocus>
					<!--end::Email-->
				</div>

				<!--begin::Submit button-->
				<div class="d-grid">
					<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
						<span class="indicator-label">
							{{ _lanq('lara-common::auth.button.send_password_reset_link') }}
						</span>
						<span class="indicator-progress">
							<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
						</span>
					</button>
				</div>
				<!--end::Submit button-->

				<hr class="mt-8 mb-6">
				<div class="row">
					<div class="col-sm-12">
						<a href="{{ route('login') }}">
							{{ _lanq('lara-common::auth.button.back_to_login') }}
						</a>
					</div>
				</div>

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