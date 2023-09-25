<div class="row index-toolbar">

	<!-- Title -->
	<div class="col-3">
		<h1>{{ title_case(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.profile.profile_title')) }}</h1>
	</div>

	<!-- Message -->
	<div class="col-6">
		@include('flash::message')
	</div>

	<div class="col-3 text-end">
		{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-sm btn-danger save-button') }}
	</div>
</div>


