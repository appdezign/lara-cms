<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

	<!--begin::Logo-->
	<div class="app-sidebar-logo @if($isbuilder) builder @endif px-9" id="kt_app_sidebar_logo">
		<div class="text-logo">
			Lara {{ $laraversion->major }}
		</div>
	</div>
	<!--end::Logo-->

	<!--begin::sidebar menu-->
	<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
		<!--begin::Menu wrapper-->
		<div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
		     data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
		     data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
		     data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
		     data-kt-scroll-save-state="true">
			<!--begin::Menu-->
			<div class="menu menu-column menu-sub-indention px-3" id="#kt_app_sidebar_menu"
			     data-kt-menu="true" data-kt-menu-expand="false">

				@if($isbuilder)

					<div class="menu-item">
						<a class="menu-link"
						   href="{{ route('admin.dashboard.index') }}">
						<span class="menu-icon">
							<span class="fa-icon fa-icon-2">
								<i class="fad fa-home-alt"></i>
							</span>
						</span>
							<span class="menu-title">
							Dashboard
						</span>
						</a>
					</div>

				@else

					@foreach($sidebarMenu as $menuKey => $menuValue)

						@if($menuValue['chld'] == '')
							<div class="menu-item">
								<a class="menu-link @if($menuKey == $activeModule) active @endif"
								   href="{{ route('admin.'. $menuValue['slug'] . '.index') }}">
									<span class="menu-icon">
										<span class="fa-icon fa-icon-2">
											<i class="{{ $menuValue['icon'] }}"></i>
										</span>
									</span>
									<span class="menu-title">
										{{ ucfirst(_lanq('lara-admin::mainmenu.items.' . strtolower($menuValue['name']))) }}
									</span>
								</a>
							</div>
						@else

							<div data-kt-menu-trigger="click"
							     class="menu-item menu-accordion @foreach($menuValue['chld'] as $subMenuKey => $subMenuValue) @if($subMenuKey == $activeModule) here show @endif @endforeach">

							<span class="menu-link">
								<span class="menu-icon">
									<span class="fa-icon fa-icon-2">
										<i class="{{ $menuValue['icon'] }}"></i>
									</span>
								</span>
								<span class="menu-title">
									{{ ucfirst(_lanq('lara-admin::mainmenu.items.' . strtolower($menuValue['name']))) }}
								</span>
								<span class="menu-arrow"></span>
							</span>

								<div class="menu-sub menu-sub-accordion menu-active-bg">
									@foreach($menuValue['chld'] as $subMenuKey => $subMenuValue)
										<div class="menu-item">
											<a class="menu-link @if($subMenuKey == $activeModule) active @endif"
											   href="{{ route('admin.'. $subMenuValue['slug'] . '.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
													<span class="menu-title">
												{{ ucfirst(_lanq('lara-admin::mainmenu.items.' . strtolower($subMenuValue['name']))) }}
												</span>
											</a>
										</div>
									@endforeach
								</div>
							</div>

						@endif
					@endforeach

				@endif

			</div>
			<!--end::Menu-->
		</div>
		<!--end::Menu wrapper-->
	</div>
	<!--end::sidebar menu-->
</div>
<!--end::Sidebar-->