<div id="kt_activities" class="builder-menu drawer drawer-end" data-kt-drawer="true" data-kt-drawer-name="activities" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'225px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_activities_toggle" data-kt-drawer-close="#kt_activities_close">

	<div class="card shadow-none border-0 rounded-0">
		<!--begin::Header-->
		<div class="card-header" id="kt_activities_header">
			<h3 class="card-title">Lara Builder</h3>

			<div class="card-toolbar">
				<button type="button" class="btn btn-sm btn-icon me-n5" id="kt_activities_close">
					<span class="fa-icon fa-icon-1">
						<i class="fal fa-times"></i>
					</span>
				</button>
			</div>
		</div>
		<!--end::Header-->

		<!--begin::Body-->
		<div class="card-body position-relative" id="kt_activities_body">
			<!--begin::Content-->
			<div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body" data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer" data-kt-scroll-offset="5px">

				<div class="menu menu-column menu-sub-indention px-3" id="#kt_app_sidebar_menu"
				     data-kt-menu="true" data-kt-menu-expand="false">

					<!-- menu single item -->
					<div class="menu-item">
						<a class="menu-link" href="{{ route('admin.entity.index') }}">
						<span class="menu-icon">
							<span class="fa-icon fa-icon-2">
								<i class="fad fa-dice-d6"></i>
							</span>
						</span>
							<span class="menu-title">Content Builder</span>
						</a>
					</div>

					<!-- menu single item -->
					<div class="menu-item">
						<a class="menu-link" href="{{ route('admin.form.index') }}">
						<span class="menu-icon">
							<span class="fa-icon fa-icon-2">
								<i class="fad fa-poll-h"></i>
							</span>
						</span>
							<span class="menu-title">Form Builder</span>
						</a>
					</div>

					<!-- menu single item -->
					<div class="menu-item">
						<a class="menu-link" href="{{ route('admin.entitygroup.index') }}">
						<span class="menu-icon">

							<span class="fa-icon fa-icon-2">
								<i class="fad fa-copy"></i>
							</span>
						</span>
							<span class="menu-title">Entity Groups</span>
						</a>
					</div>

				</div>

			</div>
			<!--end::Content-->
		</div>
		<!--end::Body-->

	</div>
</div>