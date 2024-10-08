@extends('lara-common::layout-auth')

@section('head-after')

	<style>
		.update-alert {
			margin-bottom: 2rem;
			padding: 1rem 1.5rem;
			border: none;
		}
		.update-alert ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}
		.update-alert ul li {
			color: rgb(216, 27, 96);
		}
	</style>

@endsection

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
					<img src="{{ asset('assets/admin/img/lara8-logo.svg') }}" width="48" alt="Lara" class="me-4"/>
					<h1 class="m-0">Lara {{ config('lara.lara_maj_ver') }}</h1>
				</div>

				@if($updates->lara || $updates->translation || $updates->eve)
					<div class="update-alert alert alert-info alert-important">
						<ul>
							@if($updates->lara)
								<li>Lara DB was updated to {{ $updates->lara }}</li>
							@endif
							@if($updates->translation)
								<li>Translations were updated to {{ $updates->translation }}</li>
							@endif
							@if($updates->eve)
								<li>Your application was updated to {{ $updates->eve }}</li>
							@endif
						</ul>
					</div>
				@endif

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
