@extends('lara-admin::layout2')

@section('head-after')

	@include('lara-admin::_scripts.tiny')

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

									@if($entity->hasTags())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_tags">

												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.tags') }}
												</div>
												<div class="d-block d-md-none">
													<i class="fal fa-tags"></i>
												</div>
											</button>
										</li>
									@endif

									@if($entity->hasImages())
										@if($entity->getEntityKey() != 'larawidget' || ($entity->getEntityKey() == 'larawidget' && $data->object->type != 'entity'))
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
									@endif

									@if($entity->hasVideos() || $entity->hasVideoFiles())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_videos">
												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.videos') }}
												</div>
												<div class="d-block d-md-none">
													<i class="far fa-video"></i>
												</div>
											</button>
										</li>
									@endif

									@if($entity->hasFiles())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_files">
												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.files') }}
												</div>
												<div class="d-block d-md-none">
													<i class="fal fa-paperclip"></i>
												</div>
											</button>
										</li>
									@endif

									@if($entity->hasRelated())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_related">
												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.attachments') }}
												</div>
												<div class="d-block d-md-none">
													<i class="fal fa-share-alt"></i>
												</div>
											</button>
										</li>
									@endif

									@if($entity->hasLayout())
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_layout">
												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.layout') }}
												</div>
												<div class="d-block d-md-none">
													<i class="bi bi-layout-text-window-reverse"></i>
												</div>
											</button>
										</li>
									@endif

									@if($entity->getEntityKey() == 'larawidget')
										<li class="nav-item">
											<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
											        data-bs-target="#tab_onpages">
												<div class="d-none d-md-block">
													{{ _lanq('lara-admin::default.tab.widget_pages') }}
												</div>
												<div class="d-block d-md-none">
													<i class="fal fa-clone"></i>
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

										@include('lara-admin::_partials.langversions')

										@include('lara-admin::_partials.geomap')

										@include($data->partials['opengraph'])

										@include($data->partials['seo'])

										@include($data->partials['groups'])

										@include($data->partials['author'])

										@include($data->partials['sync'])

									</div>

									@if($entity->hasTags())
										<div class="tab-pane fade" id="tab_tags" role="tabpanel">
											@include('lara-admin::_partials.tags')
										</div>
									@endif

									@if($entity->hasImages())
										<div class="tab-pane fade" id="tab_images" role="tabpanel">
											@include('lara-admin::_partials.images')
										</div>
									@endif


									@if($entity->hasVideos() || $entity->hasVideoFiles())
										<div class="tab-pane fade" id="tab_videos" role="tabpanel">

											<!-- Nav tabs -->
											<ul class="nav nav-tabs nav-video-tabs" role="tablist">
												@if($entity->hasVideos())
													<li class="nav-item" role="presentation">
														<button class="nav-link subtabs @if($data->object->videofiles->count() == 0) active @endif" id="home-tab" data-bs-toggle="tab" data-bs-target="#vidembed" type="button" role="tab">
															Embed ({{ $data->object->videos->count() }})
														</button>
													</li>
												@endif
												@if($entity->hasVideoFiles())
													<li class="nav-item" role="presentation">
														<button class="nav-link subtabs @if($data->object->videofiles->count() > 0) active @endif" id="home-tab" data-bs-toggle="tab" data-bs-target="#vidupload" type="button" role="tab">
															Upload ({{ $data->object->videofiles->count() }})
														</button>
													</li>
												@endif
											</ul>

											<!-- Tab panes -->
											<div class="tab-content pt-8">
												@if($entity->hasVideos())
													<div role="tabpanel"
													     class="tab-pane fade @if($data->object->videofiles->count() == 0) show active @endif"
													     id="vidembed">
														@include('lara-admin::_partials.videos')
													</div>
												@endif
												@if($entity->hasVideoFiles())
													<div role="tabpanel"
													     class="tab-pane fade @if($data->object->videofiles->count() > 0) show active @endif"
													     id="vidupload">
														@include('lara-admin::_partials.videofiles')
													</div>
												@endif
											</div>

										</div>
									@endif

									@if($entity->hasFiles())
										<div class="tab-pane fade" id="tab_files" role="tabpanel">
											@include('lara-admin::_partials.files')
										</div>
									@endif

									@if($entity->hasRelated())
										<div class="tab-pane fade" id="tab_related" role="tabpanel">
											@include('lara-admin::_partials.related')
										</div>
									@endif

									@if($entity->hasLayout())
										<div class="tab-pane fade" id="tab_layout" role="tabpanel">
											@include('lara-admin::_partials.layout')
										</div>
									@endif

									@if($entity->getEntityKey() == 'larawidget')
										<div class="tab-pane fade" id="tab_onpages" role="tabpanel">
											@include('lara-admin::_partials.onpages')
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

	{{ html()->hidden('_entity_key', $entity->getEntityKey()) }}

	@if($entity->hasLanguage())
		{{ html()->hidden('language', $data->object->language) }}
	@endif

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
