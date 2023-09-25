<x-formrow>
	<x-slot name="label">
		{{ html()->label('Purge ALL content:', 'langpurge') }}
	</x-slot>
	<div class="select-two-lg">
		{{ html()->select('langpurge', $data->languages, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
	</div>
</x-formrow>

<div class="row">
	<div class="col-12 col-md-2">
	</div>
	<div class="col-12 col-md-10 col-lg-9">
		@if($data->force)
			{{ html()->button('go', 'submit')->class('btn btn-danger btn-flat save-button') }}
		@else
			{{ html()->button('go', 'submit')->class('btn btn-danger btn-flat save-button')->disabled() }}
		@endif
	</div>
</div>
