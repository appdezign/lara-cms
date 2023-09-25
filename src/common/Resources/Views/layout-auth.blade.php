<!DOCTYPE html>
<html>
<head>

	@yield('head-before')

	@include('lara-common::_main.html_header')

	@yield('head-after')

</head>

<body id="kt_body" class="app-blank app-blank bgi-size-cover bgi-position-center bgi-no-repeat"
      style="background-image: url('/assets/admin/img/auth-bg.jpg')">

<!--begin::Root-->
<div class="d-flex flex-column flex-root" id="kt_app_root">

	@if(config('lara.has_frontend'))
		<div class="d-flex justify-content-between">
			<div></div>
			<div class="app-navbar-item">
				<a href="{{ route('special.home.show') }}"
				   class="btn btn-icon w-30px h-30px w-md-40px h-md-40px">
						<span class="svg-icon svg-icon-2 svg-icon-md-1 svg-icon-white">
							@include('lara-admin::_icons.front_svg')
						</span>
				</a>
			</div>
		</div>
	@endif


	<!--begin::Authentication - Sign-in -->
	<div class="d-flex justify-content-center align-items-center h-100">

		@yield('content')

	</div>
	<!--end::Authentication - Sign-in-->
</div>
<!--end::Root-->

@yield('scripts-before')

@include('lara-common::_main.html_footer')

@yield('scripts-after')

</body>
</html>
