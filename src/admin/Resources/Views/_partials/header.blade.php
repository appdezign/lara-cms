<!--begin::Header-->
<div id="kt_app_header" class="app-header @if($isbuilder) builder @endif">
	<!--begin::Header container-->
	<div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
	     id="kt_app_header_container">

		<!--begin::Sidebar mobile toggle-->
		<div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
			<div class="sidebar-mobile-toggle btn btn-icon btn-active-color-primary" id="kt_app_sidebar_mobile_toggle">
				<i class="las la-bars"></i>
			</div>
		</div>
		<!--end::Sidebar mobile toggle-->

		<!--begin::Mobile logo-->
		<div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
			<a href="#" class="app-sidebar-mobile-logo d-lg-none">
				<div class="text-logo">
					Lara {{ $laraversion->major }}
					<div class="text-logo-minor">
						{{ $laraversion->minor }}
					</div>
				</div>
			</a>
		</div>
		<!--end::Mobile logo-->

		<!--begin::Header wrapper-->
		<div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">

			<!--begin::Menu left (empty) -->
			<div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true"
			     data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}"
			     data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end"
			     data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
			     data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
			     data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
				<div class="menu menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
				     id="kt_app_header_menu" data-kt-menu="true">

				</div>
			</div>
			<!--end::Menu left (empty) -->

			<!--begin::Menu right -->
			<div class="app-navbar flex-shrink-0">

				<!-- help -->
				<div class="app-navbar-item ms-1 ms-md-3">
					<a href="{{ config('lara-admin.manual.online.url') }}" target="_blank"
					   class="btn btn-icon btn-custom w-30px h-30px w-md-40px h-md-40px">
						<span class="fa-icon fa-icon-2 fa-icon-md-1">
							<i class="fad fa-question-circle"></i>
						</span>
					</a>
				</div>

				<!-- languages -->
				@if(!$isbuilder && isset($clanguage))
					<div class="app-navbar-item ms-1 ms-md-3">
						<a href="#"
						   class="btn btn-icon w-30px h-30px w-md-40px h-md-40px @if(empty($entity) || !$entity->hasLanguage()) disabled @endif"
						   data-kt-menu-trigger="{default:'click'}" data-kt-menu-attach="parent"
						   data-kt-menu-placement="bottom-end">
							{{ strtoupper($clanguage) }}
						</a>
						<div class="menu menu-sub menu-sub-dropdown menu-column menu-title-gray-700 menu-icon-muted menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
						     data-kt-menu="true" data-kt-element="theme-mode-menu">

							@foreach($clanguages as $clang)
								<div class="menu-item px-3 my-0">
									<a href="{!! route('admin.'.$entity->getEntityRouteKey().'.index', ['clanguage' => $clang->code, 'resetfilters' => 'true']) !!}"
									   class="menu-link px-3 py-2">
										<span class="menu-title">{{ strtoupper($clang->code) }}</span>
									</a>
								</div>
							@endforeach
						</div>
					</div>
				@endif

				<!-- cache -->
				<div class="app-navbar-item ms-1 ms-md-3">
					<a href="{!! route('admin.cache.index') !!}"
					   class="btn btn-icon btn-custom w-30px h-30px w-md-40px h-md-40px">
						<span class="fa-icon fa-icon-2 fa-icon-md-1">
							<i class="fad fa-database"></i>
						</span>
					</a>
				</div>


				<!-- user profile -->
				<div class="app-navbar-item ms-1 ms-md-3">
					<a href="#"
					   class="btn btn-icon w-30px h-30px w-md-90px h-md-40px"
					   data-kt-menu-trigger="{default:'click'}" data-kt-menu-attach="parent"
					   data-kt-menu-placement="bottom-end">
						<span class="fa-icon fa-icon-2 fa-icon-md-1">
							<i class="fad fa-user-circle"></i>
						</span>
						<div class="username ms-1 d-none d-md-inline-block">
							{{ Auth::user()->username }}
						</div>
					</a>
					<div class="menu menu-sub menu-sub-dropdown menu-column menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
					     data-kt-menu="true">
						<div class="menu-item px-3">
							<div class="menu-content d-flex justify-content-center px-3">
								<span class="fw-bold fs-5">{{ Auth::user()->name }}</span>
							</div>
						</div>
						<div class="separator my-2"></div>
						<div class="menu-item px-5">
							<a href="{{ route('admin.user.profile') }}" class="menu-link px-5">
								{{ _lanq('lara-admin::user.profile.link_text') }}
							</a>
						</div>
						<div class="menu-item px-5">
							<a href="{{ route('logout') }}" class="menu-link px-5"
							   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								{{ _lanq('lara-admin::user.menu.logout_text') }}
							</a>

							{{ html()->form('POST', route('logout'))
								->id('logout-form')
								->attributes(['accept-charset' => 'UTF-8'])
								->open() }}
							<input type="hidden" name="redirect" value="admin.dashboard.index">
							{{ html()->form()->close() }}

						</div>
					</div>
				</div>

				@if(!$isbuilder && config('lara.has_frontend'))
					<!-- front -->
					<div class="app-navbar-item ms-1 ms-md-3">
						<a href="{{ route('special.home.show') }}"
						   target="_blank"
						   class="btn btn-icon w-30px h-30px w-md-40px h-md-40px">
							<span class="fa-icon fa-icon-2 fa-icon-md-1">
								<i class="fad fa-external-link"></i>
							</span>
						</a>
					</div>
				@endif

				@if(Auth::user()->isAn('administrator'))
					<!-- builder -->
					<div class="app-navbar-item ms-1 ms-md-3">
						<div class="btn btn-icon btn-custom w-30px h-30px w-md-40px h-md-40px"
						     id="kt_activities_toggle">
							<span class="fa-icon fa-icon-2 fa-icon-md-1">
								<i class="fad fa-layer-group"></i>
							</span>
						</div>
					</div>
				@endif

			</div>
			<!--end::Menu right -->

		</div>
		<!--end::Header wrapper-->
	</div>
	<!--end::Header container-->
</div>
<!--end::Header-->