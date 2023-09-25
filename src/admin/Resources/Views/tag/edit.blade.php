@extends('lara-admin::layout2')

@section('head-after')

	@if($entity->hasTinyBody() || $entity->hasTinyLead())
		@include('lara-admin::_scripts.tiny')
	@endif

@endsection

@section('content')

	{{ html()->modelForm($data->object,
			'PATCH',
			route('admin.'.$entity->getEntityRouteKey().'.update', ['id' => $data->object->id]))
			->id('lara-default-edit-form')
			->attributes(['accept-charset' => 'UTF-8'])
			->class('needs-validation')
			->novalidate()
			->open() }}

	@include($data->partials['header'])

	<!--begin::Wrapper-->
	<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
		<!--begin::Main-->
		<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
			<!--begin::Content wrapper-->
			<div class="d-flex flex-column flex-column-fluid">

				@include($data->partials['pagetitle'])

				<!--begin::Content-->
				<div id="kt_app_content" class="app-content  flex-column-fluid">
					<!--begin::Content container-->
					<div id="kt_app_content_container" class="app-container container">

						<div class="row">
							<div class="col-12 col-lg-10 offset-lg-1">

								<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">

									<li class="nav-item">
										<button class="nav-link js-first-tab" type="button" role="tab" data-bs-toggle="tab"
										        data-bs-target="#tab_content">
											<div class="d-none d-md-block">
												{{ _lanq('lara-admin::default.tab.content') }}
											</div>
											<div class="d-block d-md-none">
												<i class="far fa-file-alt"></i>
											</div>
										</button>
									</li>

									@if($entity->hasImages())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_images">

												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.images') }}
												</div>
												<div class="d-block d-md-none">
													<i class="far fa-file-image"></i>
												</div>

											</button>
										</li>
									@endif

								</ul>

								<div class="tab-content" id="myTabContent">

									<div class="tab-pane fade" id="tab_content" role="tabpanel">

										@include($data->partials['status'])

										<div class="box box-default">

											<x-boxheader cstate="active" collapseid="content">
												{{ _lanq('lara-admin::default.boxtitle.content') }}
											</x-boxheader>

											<div id="kt_card_collapsible_content" class="collapse show">
												<div class="box-body">
													@include($data->partials['content'])
												</div>
											</div>

										</div>

										@include($data->partials['seo'])

									</div>

									@if($entity->hasImages())
										<div class="tab-pane fade" id="tab_images" role="tabpanel">
											@include('lara-admin::_partials.images')
										</div>
									@endif

								</div>

							</div>
						</div>

					</div>
					<!--end::Content container-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Content wrapper-->
		</div>
		<!--end:::Main-->
	</div>
	<!--end::Wrapper-->

	{{ html()->hidden('language', $clanguage) }}

	{{ html()->closeModelForm() }}

@endsection

@section('scripts-after')

	@if($entity->hasImages() || $entity->hasFiles())
		@include('lara-admin::_scripts.dropzone2')
	@endif

	@if($entity->hasFields())
		@include('lara-admin::_scripts.fields')
	@endif

	@include('lara-admin::_scripts.misc')

@endsection