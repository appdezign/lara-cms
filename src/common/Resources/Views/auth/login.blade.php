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
				<form role="form" method="POST" action="{{ route('login') }}">

					{!! csrf_field()  !!}
					<input type="hidden" name="_login_type" value="backend">

					<div class="d-flex justify-content-center align-items-center mb-12">
						<img src="{{ asset('assets/admin/img/lara75-logo.svg') }}" width="48" alt="Lara" class="me-4"/>
						<h1 class="m-0" class="lara-admin-logo-title">Lara 7</h1>
					</div>

					<div class="fv-row mb-8">
						<!--begin::Email-->
						<input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}"
						       placeholder="{{ _lanq('lara-common::auth.loginform.placeholder_email') }}" required
						       autofocus>
						<!--end::Email-->
					</div>

					<div class="fv-row mb-12">
						<!--begin::Password-->
						<input id="password" type="password" class="form-control" name="password"
						       placeholder="{{ _lanq('lara-common::auth.loginform.placeholder_password') }}" required>
						<!--end::Password-->
					</div>

					<!--begin::Submit button-->
					<div class="d-grid">
						<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
							<span class="indicator-label">login</span>
							<span class="indicator-progress">
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
							</span>
						</button>
					</div>
					<!--end::Submit button-->
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
