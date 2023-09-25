<div class="title-bar">
	<div class="app-container container">
		<div class="row">
			<div class="col-md-10 offset-md-1">
				<div class="title-bar-inner">
					<h1 class="page-heading text-dark fw-light fs-4">
						{{ title_case(_lanq('lara-admin::entity.form.entity_title')) }}
					</h1>
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



