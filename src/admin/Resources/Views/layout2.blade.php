<!DOCTYPE html>
<html lang="en">
<head>

	@yield('head-before')

	@include('lara-admin::_main.html_header')

	@yield('head-after')

</head>

<body id="kt_app_body" data-kt-app-layout="light-header" data-kt-app-header-fixed="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default module-{{ $entity->getEntityKey() }} module-{{ $entity->getEntityKey() }}-{{ $entity->getMethod() }}">

<!--begin::loader-->
<div class="app-page-loader">
	<span class="spinner-border text-primary" role="status">
		<span class="visually-hidden">Loading...</span>
	</span>
</div>
<!--end::Loader-->

<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
	<!--begin::Page-->
	<div class="app-page flex-column flex-column-fluid" id="kt_app_page">

		@yield('content')

	</div>
	<!--end::Page-->
</div>
<!--end::App-->

@yield('scripts-before')

@include('lara-admin::_main.html_footer')

@yield('scripts-after')

</body>
</html>

