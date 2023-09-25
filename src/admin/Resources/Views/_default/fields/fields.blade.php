@foreach($entity->getCustomColumns() as $cvar)

		<?php
		$cvarfieldname = $cvar->fieldname;
		$cvarvalue = $data->object->$cvarfieldname;
		$cvarstate = getFieldState($data->object, $cvar);
		$cvardisabled = ($cvar->fieldstate == 'disabled' && $entity->getMethod() == 'edit');
		?>

	{{-- available hooks: before, between, after --}}
	@if($cvar->fieldhook == $fhook)

		@if(\View::exists('lara-' . $entity->getModule() . '::'.$entity->getEntityKey().'.fields.'.$cvar->fieldname))
			@include('lara-' . $entity->getModule() . '::'.$entity->getEntityKey().'.fields.'.$cvar->fieldname)
		@elseif($cvar->fieldtype == 'custom')
			@includeIf('lara-' . $entity->getModule() . '::'.$entity->getEntityKey().'.fields.'.$cvar->fieldname)
		@elseif($cvar->fieldtype == 'geolocation')
			@include('lara-admin::_partials.geolocation', ['cvar' => $cvar])
		@else

			@if($cvarstate === true)

				<x-formrow>

					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.' .$cvar->fieldname) .':', $cvar->fieldname) }}
					</x-slot>

					@if($cvar->fieldtype == 'string')
						{{ html()->text($cvar->fieldname, null)->class('form-control')
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'text')
						{{ html()->textarea($cvar->fieldname, null)->class('form-control')->rows(4)
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'mcefull')
						@if($cvardisabled)
							{{ html()->textarea($cvar->fieldname, null)->class('form-control')->rows(4)->disabled() }}
						@else
							{{ html()->textarea($cvar->fieldname, null)->class('form-control tiny')->rows(4) }}
						@endif
					@endif

					@if($cvar->fieldtype == 'mcemin')
						@if($cvardisabled)
							{{ html()->textarea($cvar->fieldname, null)->class('form-control')->rows(4)->disabled() }}
						@else
							{{ html()->textarea($cvar->fieldname, null)->class('form-control tinymin')->rows(4) }}
						@endif
					@endif

					@if($cvar->fieldtype == 'email')
						{{ html()->email($cvar->fieldname, null)->class('form-control')
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'datetime' || $cvar->fieldtype == 'date' || $cvar->fieldtype == 'time')

						<div id="dtp-{{ $cvar->fieldname }}" class="date-flat-pickr">
							{{ html()->text($cvar->fieldname, null)->class('form-control')->data('input')
								->if($cvardisabled, function ($el) {
									return $el->disabled();
								}) }}
							<a class="flat-pickr-button" title="toggle" data-toggle>
								<i class="fal fa-calendar-alt"></i>
							</a>
						</div>

					@endif

					@if($cvar->fieldtype == 'integer')

						{{ html()->input('number', $cvar->fieldname, null)->class('form-control')
							->attributes(['step' => '1'])
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'intunsigned')
						{{ html()->input('number', $cvar->fieldname, null)->class('form-control')
							->attributes(['min' => '0', 'step' => '1'])
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if(substr($cvar->fieldtype,0,7) == 'decimal')
						{{ html()->input('number', $cvar->fieldname, null)->class('form-control')
							->attributes(['min' => '0', 'step' => 'any'])
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'boolean')
						<div class="form-check">
							@if(!$cvardisabled)
								{{ html()->hidden($cvar->fieldname, 0) }}
							@endif
							{{ html()->checkbox($cvar->fieldname, null, 1)->class('form-check-input')
								->if($cvardisabled, function ($el) {
									return $el->disabled();
								}) }}
						</div>
					@endif

					@if($cvar->fieldtype == 'video')
						{{ html()->text($cvar->fieldname, null)->class('form-control')
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
						@if(!empty($cvarvalue))
							<div class="ratio ratio-16by9 mt-8 mb-10">
								<iframe width="560" height="315"
								        src="https://www.youtube.com/embed/{{ $cvarvalue }}?rel=0"
								        frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
							</div>
						@endif
					@endif


					@if($cvar->fieldtype == 'selectone')
						{{ html()->select($cvar->fieldname, $cvar->fieldvalues, null)->class('form-select form-select-sm')
							->data('control', 'select2')->data('hide-search', 'true')
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					@if($cvar->fieldtype == 'selectone2one')
						{{-- make sure the values are only used once (per language) --}}
							<?php
							$cvar->available = array();
							$active = $entity->getEntityModelClass()::langIs($clanguage)->distinct($cvar->fieldname)->orderBy($cvar->fieldname)->pluck($cvar->fieldname)->toArray();
							$available = array();
							foreach ($cvar->fieldvalues as $value) {
								if (!in_array($value, $active)) {
									$available[$value] = $value;
								}
							}
							$cvar->available = $available;
							$fielddata = processFieldData($cvar->available, $cvar->fieldname, $data->object);
							?>
						{{ html()->select($cvar->fieldname, $fielddata, null)->class('form-select form-select-sm')
							->data('control', 'select2')->data('hide-search', 'true')
							->if($cvardisabled, function ($el) {
								return $el->disabled();
							}) }}
					@endif

					{{-- add hidden field, if field is disabled --}}
					@if($cvardisabled)
						{{ html()->hidden($cvar->fieldname, null) }}
					@endif

				</x-formrow>

			@else

				{{-- Hide fields --}}
				{{ html()->hidden($cvar->fieldname, null) }}

			@endif

		@endif

	@endif

@endforeach

