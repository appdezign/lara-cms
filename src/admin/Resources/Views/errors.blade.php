<!DOCTYPE html>
<html lang="en">
<head>

	@yield('head-before')

	@include('lara-admin::_main.html_header')

	@yield('head-after')

</head>

<body id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" data-kt-app-toolbar-fixed="false" data-kt-app-footer-fixed="false" class="app-default module-{{ $entity->getEntityKey() }} module-{{ $entity->getEntityKey() }}-{{ $entity->getMethod() }}">

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

		@include('lara-admin::errors._partials.header')

		<!--begin::Wrapper-->
		<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

			@include('lara-admin::_partials.menu')

			<!--begin::Main-->
			<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
				<!--begin::Content wrapper-->
				<div class="d-flex flex-column flex-column-fluid">

					@yield('content')

				</div>
				<!--end::Content wrapper-->

				@include('lara-admin::_partials.footer')

			</div>
			<!--end:::Main-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Page-->
</div>
<!--end::App-->

@include('lara-admin::_partials.builder_menu')


@yield('scripts-before')

@include('lara-admin::_main.html_footer')

@yield('scripts-after')

</body>
</html>

