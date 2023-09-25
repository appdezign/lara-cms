<div class="row page-title module-page-title mt-4">

	<!--begin:Page Title-->
	<div class="col-6 col-sm-3 order-1 order-sm-1">
		<h1 class="page-heading text-dark fw-light fs-1 my-0">
			{{ title_case(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_title')) }}
		</h1>
	</div>
	<!--end:Page Title-->

	<!--begin:Message-->
	<div class="col-12 col-sm-6 order-3 order-sm-2">
		@include('flash::message')
	</div>
	<!--end:Message-->

	<!--begin:Tools-->
	<div class="col-6 col-sm-3 order-2 order-sm-3">

		@if($data->filters->missing == false)

			{{ html()->form('GET', route('admin.'.$entity->getEntityRouteKey().'.index'))
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

			<div class="d-flex align-items-center position-relative w-100 m-0">
				<span class="svg-icon svg-icon-3 svg-icon-gray-500 position-absolute top-50 ms-5 translate-middle-y">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
					<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
					</svg>
				</span>

				{{ html()->text('keywords', old('keywords', null))->class('form-control form-control-sm form-control-search ps-13')->placeholder(_lanq('lara-' . $entity->getModule().'::' . $entity->getEntityKey().'.form.search')) }}
			</div>

			{{ html()->form()->close() }}

		@endif

	</div>
	<!--end:Tools-->

</div>
