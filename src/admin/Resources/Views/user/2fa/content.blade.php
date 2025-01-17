
@if(config('lara.force_2fa') && !$data->object->hasTwoFactor())
	<div class="alert alert-danger alert-important mb-6" role="alert">
		{{ _lanq('lara-admin::2fa.message.force_is_enabled') }}
	</div>
@endif

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="fields">
		{{ _lanq('lara-admin::2fa.boxtitle.2fa') }}
	</x-boxheader>

	<div id="kt_card_collapsible_fields" class="collapse show">
		<div class="box-body">

			@if($data->object->hasTwoFactor())
				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.status') }}
					</x-slot>
					<span style="color: #009664; font-size: 24px; font-weight: bold;">
					{{ strtoupper(_lanq('lara-admin::2fa.value.status_enabled')) }}
					</span>

					<div class="py-4">
						{{ _lanq('lara-admin::2fa.message.is_enabled') }}
					</div>

				</x-formrow>
				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.qrcode') }}
					</x-slot>
					<img src="data:image/png;base64, <?php echo $data->qrCodeImage; ?> "/>
				</x-formrow>
				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.recovery_codes') }}
					</x-slot>
					<span style="font-family: 'Courier New', serif">
						@foreach($data->object->recoverCodes as $recoveryCode)
							{{ $recoveryCode }}<br>
						@endforeach
					</span>
				</x-formrow>
				<hr class="my-16">
				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.action') }}
					</x-slot>
					{{ html()->input('submit', '_deactivate_2fa', _lanq('lara-admin::2fa.button.deactivate_2fa_now'))->class('btn btn-sm btn-danger') }}
				</x-formrow>
			@else
				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.status') }}
					</x-slot>
					<span style="color: #d81b60; font-size: 16px; font-weight: bold;">
					{{ strtoupper(_lanq('lara-admin::2fa.value.status_disabled')) }}
					</span>
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.action') }}
					</x-slot>
					{{ html()->input('submit', '_activate_2fa', _lanq('lara-admin::2fa.button.activate_2fa_now'))->class('btn btn-sm btn-danger') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-admin::2fa.column.qrcode') }}
					</x-slot>
					{{ html()->hidden('_new_secret_key', $data->newSecretKey) }}
					<img src="data:image/png;base64, <?php echo $data->qrCodeImage; ?> "/>
				</x-formrow>
			@endif

		</div>
	</div>

</div>















