@extends('lara-common::layout-auth')

@section('content')

	<!--begin::Card-->
	<div class="card rounded-2 w-md-400px">

		<div class="card-header d-flex flex-column pt-6 pb-6 justify-content-center align-items-center">
			<img src="{{ asset('assets/admin/img/lara8-logo.svg') }}" width="48" alt="Lara" class="mb-6"/>
			<h1 class="fs-2">
				{{ ucfirst(_lanq('lara-common::auth.passwordforgot.password_reset_title')) }}
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

			@if($email)

				<form class="form-horizontal" role="form" method="POST"
				      action="{{ route('password.request') }}">

					{{ csrf_field() }}

					<input type="hidden" name="token" value="{{ $token }}">

					<div class="fv-row mb-8">
						<label for="email">{{ _lanq('lara-common::auth.field.email') }}</label>

						<input id="email" name="email" type="email" value="{{ $email }}" class="form-control" disabled>
						<input id="email" type="hidden" name="email" value="{{ $email }}">
					</div>


					<div class="fv-row mb-8">
						<label for="password">{{ _lanq('lara-common::auth.field.password') }}</label>

						<input id="password" type="password" class="form-control" name="password"
						       required>
					</div>

					<div class="fv-row mb-8">
						<label for="password-confirm">{{ _lanq('lara-common::auth.field.confirm_password') }}</label>
						<input id="password-confirm" type="password" class="form-control"
						       name="password_confirmation" required>

					</div>

					<div class="d-grid">
						<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
						<span class="indicator-label">
							{{ _lanq('lara-common::auth.button.reset_password') }}
						</span>
							<span class="indicator-progress">
							<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
						</span>
						</button>
					</div>

				</form>
			@else
				<div class="alert alert-warning">
					{{ _lanq('lara-common::auth.field.email_not_found') }}
				</div>
			@endif

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