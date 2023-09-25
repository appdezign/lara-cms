<div class="title-bar">
	<div class="app-container container">
		<div class="row">
			<div class="col-md-10 offset-md-1">
				<div class="title-bar-inner">
					<div class="d-none d-md-block">
						<h1 class="page-heading text-dark fw-light fs-4">
							{{ title_case(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_single')) }}
							- <em>{{ str_limit($data->object->title, 25, ' ..') }}</em>
						</h1>
					</div>
					<div class="d-block d-md-none">
						<h1 class="page-heading text-dark fw-light fs-1 my-0">
							<em>{{ str_limit($data->object->title, 25, ' ..') }}</em>
						</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="message-bar">
	<div class="app-container container">
		<div class="row">
			<div class="col-md-8 offset-md-2">
				<div class="message-bar-inner">
					@if ($errors->any())
						<div class="alert alert-danger">
							<ul class="">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@else
						@include('flash::message')
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@if($entity->getEntityKey() == 'page' && $data->object->cgroup == 'module')
	<div class="return-bar mb-6">
		<div class="app-container container">
			<div class="row">
				<div class="col-10 offset-md-1">
					<div class="return-page-inner p-3 text-center">
						{{ _lanq('lara-admin::page.messages.page_is_module') }} -
						<span><em>{{ title_case(_lanq('lara-' . $data->modulePageModule->getModule().'::'.$data->modulePageModule->getEntityKey().'.entity.entity_title')) }}</em></span>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif

