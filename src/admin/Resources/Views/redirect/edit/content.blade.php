{{-- TITLE --}}
{{ html()->hidden('title', $data->object->title) }}

{{-- redirectfrom --}}
<div class="row form-group">
	<div class="col-12 col-md-2">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirectfrom').':', 'redirectfrom') }}
		@if($data->object->has_error == 1)
			<i class="fas fa-exclamation-circle color-danger"></i>
		@endif
	</div>
	<div class="col-12 col-md-1 p-2 text-end">/{{ $clanguage }}/</div>
	<div class="col-12 col-md-9 col-lg-8">
		{{ html()->text('redirectfrom', null)->class('form-control')->required() }}

		<div class="help-block with-errors"></div>
	</div>
</div>

{{-- redirectto --}}
<div class="row form-group">
	<div class="col-12 col-md-2">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirectto').':', 'redirectto') }}
	</div>
	<div class="col-12 col-md-1 p-2 text-end">/{{ $clanguage }}/</div>
	<div class="col-12 col-md-9 col-lg-8">
		{{ html()->text('redirectto', null)->class('form-control')->required()->style(['text-transform' => 'lowercase']) }}
		<div class="invalid-feedback">
			{{ _lanq('lara-admin::default.message.error_required_field') }}
		</div>
	</div>
</div>

{{-- redirecttype --}}
<div class="row form-group">
	<div class="col-12 col-md-2">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.redirecttype').':', 'redirecttype') }}
	</div>
	<div class="col-12 col-md-1 p-8 text-right">&nbsp;</div>
	<div class="col-12 col-md-9 col-lg-8">
		{{ html()->select('redirecttype', ['301' => '301','302' => '302', '' => 'none'], null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
	</div>
</div>

{{-- locked_by_admin --}}
<div class="row form-group">
	<div class="col-12 col-md-2">
		{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.locked_by_admin').':', 'locked_by_admin') }}
	</div>
	<div class="col-12 col-md-1 p-8 text-right">&nbsp;</div>
	<div class="col-12 col-md-9 col-lg-8">

		<div class="form-check">
			@if(Auth::user()->isAn('administrator'))
				{{ html()->hidden('locked_by_admin', 0) }}
				{{ html()->checkbox('locked_by_admin', null, 1)->class('form-check-input') }}
			@else
				{{ html()->hidden('locked_by_admin', null) }}
				{{ html()->checkbox('locked_by_admin', null, 1)->class('form-check-input')->disabled() }}
			@endif
		</div>

	</div>
</div>