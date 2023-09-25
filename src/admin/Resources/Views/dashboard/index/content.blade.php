@if(config('analytics.property_id'))

	<!--begin::row-->
	<div class="row mb-6">

		<div class="col-md-6 col-lg-5 offset-lg-1">

			<!--begin::card user stats-->
			<div class="card">
				<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
				     data-bs-target="#kt_card_collapsible_1">
					<h3 class="card-title">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.user_stats') }}
					</h3>
					<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
					</div>
				</div>
				<div id="kt_card_collapsible_1" class="collapse show">
					<div class="card-body">
						@if($data->user && $data->user->type && $data->user->sessions)
							<canvas id="user-stats"></canvas>
						@else
							<div class="panel text-center analytics-panel">
								<h5 class="color-primary fw-normal">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.nodata') }}</h5>
								<a href="{{ route('admin.dashboard.refresh', ['gatype' => 'userstats']) }}"
								   class="btn btn-icon btn-icon-danger">
									<i class="far fa-sync-alt"></i>
								</a>
							</div>
						@endif
					</div>
				</div>
			</div>
			<!--end::card user stats-->

		</div>

		<div class="col-md-6 col-lg-5">

			<!--begin::card browser stats-->
			<div class="card">
				<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
				     data-bs-target="#kt_card_collapsible_2">
					<h3 class="card-title">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.browser_stats') }}
					</h3>
					<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
					</div>
				</div>
				<div id="kt_card_collapsible_2" class="collapse show">
					<div class="card-body">
						@if($data->browser && $data->browser->type && $data->browser->sessions)
							<canvas id="browser-stats"></canvas>
						@else
							<div class="panel text-center analytics-panel">
								<h5 class="color-primary fw-normal">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.nodata') }}</h5>
								<a href="{{ route('admin.dashboard.refresh', ['gatype' => 'browserstats']) }}"
								   class="btn btn-icon btn-icon-danger">
									<i class="far fa-sync-alt"></i>
								</a>
							</div>
						@endif
					</div>
				</div>
			</div>
			<!--end::card browser stats-->

		</div>

	</div>
	<!--end::row-->

	<!--begin::row-->
	<div class="row mb-6">

		<div class="col-md-12 col-lg-10 offset-lg-1">

			<!--begin::card site stats-->
			<div class="card">
				<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
				     data-bs-target="#kt_card_collapsible_3">
					<h3 class="card-title">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.site_stats') }}
					</h3>
					<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
					</div>
				</div>
				<div id="kt_card_collapsible_3" class="collapse show">
					<div class="card-body">

						@if($data->site && $data->site->dates && $data->site->visitors && $data->site->pageviews)
							<canvas id="site-stats"></canvas>
						@else
							<div class="panel text-center analytics-panel">
								<h5 class="color-primary fw-normal">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.nodata') }}</h5>
								<a href="{{ route('admin.dashboard.refresh', ['gatype' => 'sitestats']) }}"
								   class="btn btn-icon btn-icon-danger">
									<i class="far fa-sync-alt"></i>
								</a>
							</div>
						@endif

					</div>
				</div>
			</div>
			<!--end::card site stats-->
		</div>
	</div>
	<!--end::row-->

	<!--begin::row-->
	<div class="row mb-6">
		<div class="col-md-12 col-lg-10 offset-lg-1">
			<!--begin::card page stats-->
			<div class="card">
				<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
				     data-bs-target="#kt_card_collapsible_4">
					<h3 class="card-title">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.page_stats') }}
					</h3>
					<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
					</div>
				</div>
				<div id="kt_card_collapsible_4" class="collapse show">
					<div class="card-body">

						@if($data->page && $data->page->urls && $data->page->pageviews)
							<canvas id="page-stats"></canvas>
						@else
							<div class="panel text-center analytics-panel">
								<h5 class="color-primary fw-normal">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.nodata') }}</h5>
								<a href="{{ route('admin.dashboard.refresh', ['gatype' => 'pagestats']) }}"
								   class="btn btn-icon btn-icon-danger">
									<i class="far fa-sync-alt"></i>
								</a>
							</div>
						@endif

					</div>
				</div>
			</div>
			<!--end::card page stats-->
		</div>
	</div>
	<!--end::row-->

	<!--begin::row-->
	<div class="row mb-6">
		<div class="col-md-12 col-lg-10 offset-lg-1">
			<!--begin::card ref stats-->
			<div class="card">
				<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
				     data-bs-target="#kt_card_collapsible_5">
					<h3 class="card-title">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.ref_stats') }}
					</h3>
					<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
					</div>
				</div>
				<div id="kt_card_collapsible_5" class="collapse show">
					<div class="card-body">

						@if($data->ref && $data->ref->urls && $data->ref->pageviews)
							<canvas id="ref-stats"></canvas>
						@else
							<div class="panel text-center analytics-panel">
								<h5 class="color-primary fw-normal">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.message.nodata') }}</h5>
								<a href="{{ route('admin.dashboard.refresh', ['gatype' => 'refstats']) }}"
								   class="btn btn-icon btn-icon-danger">
									<i class="far fa-sync-alt"></i>
								</a>
							</div>
						@endif

					</div>
				</div>
			</div>
			<!--end::card ref stats-->
		</div>
	</div>
	<!--end::row-->

@endif

<div class="row mb-6">

	<div class="col-md-12 col-lg-10 offset-lg-1">

		<!--begin::card users-->
		<div class="card">
			<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
			     data-bs-target="#kt_card_collapsible_6">
				<h3 class="card-title">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.users') }}
				</h3>
				<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
				</div>
			</div>
			<div id="kt_card_collapsible_6" class="collapse show">
				<div class="card-body">

					<div class="table-responsive">
						<table class="table table-lara table-row-bordered table-hover">
							<thead>
								<tr>
									<th class="w-15 text-center">
										{{ _lanq('lara-' . $entity->getModule().'::user.column.isloggedin') }}
									</th>
									<th class="w-25">
										{{ _lanq('lara-' . $entity->getModule().'::user.column.username') }}
									</th>
									<th class="w-25">
										{{ _lanq('lara-' . $entity->getModule().'::user.column.role') }}
									</th>
									<th class="w-35">
										{{ _lanq('lara-' . $entity->getModule().'::user.column.lastlogin') }}
									</th>
								</tr>
							</thead>
							<tbody>

								@foreach($data->lara_users as $lara_user)

									@if($lara_user->id == Auth::user()->id)
										<tr>
											<td class="status text-center">
												@if($lara_user->is_loggedin == 1)
													<i class="las la-check-circle status-publish"></i>
												@else
													<i class="las la-circle status-concept"></i>
												@endif
											</td>
											<td>
												<span class="color-primary">{{ $lara_user->username }}</span>
											</td>
											<td>
												<span class="color-primary">{{ $lara_user->mainrole->title }}</span>
											</td>
											<td>
												<span class="color-primary">{{ Date::parse($lara_user->last_login)->format('d M Y, H:i:s') }}</span>
											</td>

										</tr>
									@else
										<tr>
											<td class="status text-center">
												@if($lara_user->is_loggedin == 1)
													<i class="las la-check-circle status-publish"></i>
												@else
													<i class="las la-circle status-concept"></i>
												@endif
											</td>
											<td>{{ $lara_user->username }}</td>
											<td>
												@if($lara_user->mainrole)
													{{ $lara_user->mainrole->title }}
												@endif
											</td>
											<td>
												@if($lara_user->last_login)
													{{ Date::parse($lara_user->last_login)->format('d M Y, H:i:s') }}
												@else
													{{ _lanq('lara-admin::dashboard.message.never_logged_in') }}
												@endif
											</td>

										</tr>
									@endif

								@endforeach

							</tbody>
							<tfoot>
								<tr>
									<td colspan="4">

									</td>
								</tr>
							</tfoot>
						</table>
					</div>

				</div>
			</div>
		</div>
		<!--end::card users-->

	</div>
</div>
<!--end::row-->

<!--begin::row-->
<div class="row mb-6">
	<div class="col-md-12 col-lg-10 offset-lg-1">

		<!--begin::card content stats-->
		<div class="card">
			<div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse"
			     data-bs-target="#kt_card_collapsible_7">
				<h3 class="card-title">
					{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.boxtitle.content_items') }}
				</h3>
				<div class="card-toolbar rotate-180">
						<span class="svg-icon svg-icon-1">
							@include('lara-admin::_icons.arrowdown_svg')
						</span>
				</div>
			</div>
			<div id="kt_card_collapsible_7" class="collapse show">
				<div class="card-body">

					<canvas id="content-stats"></canvas>

				</div>
			</div>
		</div>
		<!--end::card content stats-->

	</div>

</div>

