@extends('lara-admin::layout2')

@section('head-after')

	@include('lara-admin::_scripts.tiny')

@endsection

@section('content')

	{{ html()->form('POST', route('admin.'.$entity->getEntityRouteKey().'.store'))
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
							<div class="col-md-10 offset-md-1">

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

								@include($data->partials['groups'])

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

	@if($entity->hasUser())
		{{ html()->hidden('user_id', Auth::user()->id) }}
	@endif

	@if($entity->hasLanguage())
		{{ html()->hidden('language', $clanguage) }}
	@endif

	{{ html()->form()->close() }}

@endsection

@section('scripts-after')

	@if($entity->hasFields())
		@include('lara-admin::_scripts.fields')
	@endif

@endsection


