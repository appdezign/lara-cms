@if($entity->hasSlug())

	<x-formrow>
		<x-slot name="label">
			{{ _lanq('lara-admin::default.column.slug') }}:
		</x-slot>
		@if($data->object->slug_lock == 1)
			<i class="las la-lock slug-lock-icon"></i>&nbsp;&nbsp;{{ $data->object->slug }}
		@else
			{{ $data->object->slug }}&nbsp;&nbsp;<a id="slug-toggle" href="javascript:void(0)" class="text-muted">edit</a>
		@endif
	</x-formrow>

	<div id="slug-reset" style="display:none">
		<x-formrow>
			<x-slot name="label">
				&nbsp;
			</x-slot>
			<div class="card card-border">

				<div class="card-body pb-6">

					<div class="row form-group">
						<div class="col">
							{{ html()->text('_new_slug', $data->object->slug)->class('form-control') }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-1">
							<div class="form-check">
								{{ html()->hidden('_slug_reset', 0) }}
								{{ html()->checkbox('_slug_reset', null, 1)->class('form-check-input') }}
							</div>
						</div>
						<div class="col-11">
							{{ _lanq('lara-admin::default.button.reset_slug') }}
						</div>
					</div>

					<div class="row form-group">
						<div class="col-1">
							<div class="form-check">
								{{ html()->hidden('slug_lock', 0) }}
								{{ html()->checkbox('slug_lock', old('slug_lock', $data->object->slug_lock), 1)->class('form-check-input') }}
							</div>
						</div>
						<div class="col-11">
							{{ _lanq('lara-admin::default.button.lock_slug') }}
						</div>
					</div>

					{{ html()->input('submit', 'saveslug', _lanq('lara-admin::default.button.update_slug'))->class('btn btn-sm btn-danger') }}

				</div>

			</div>
		</x-formrow>
	</div>

@endif