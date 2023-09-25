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
