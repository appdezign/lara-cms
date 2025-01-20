@if(config('lara.force_2fa') && !$data->object->hasTwoFactor())
	<div class="alert alert-danger alert-important mb-6" role="alert">
		{{ _lanq('lara-admin::2fa.message.force_is_enabled') }}
	</div>
@endif

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="fields">
		2FA Status
	</x-boxheader>

	<div id="kt_card_collapsible_fields" class="collapse show">
		<div class="box-body">

			<div class="row form-group">
				<div class="col-12 col-md-2">
					Status :
					@if($data->object->hasTwoFactor())
						<i class="las la-lock float-end" style="font-size:24px; color: #00967d;"></i>
					@else
						<i class="las la-unlock float-end" style="font-size:24px; color: #d81b60;"></i>
					@endif
				</div>
				<div class="col-12 col-md-5">
					@if($data->object->hasTwoFactor())
						<span style="color: #00967d; font-size: 20px; font-weight: bold;">
							{{ strtoupper(_lanq('lara-admin::2fa.value.status_enabled')) }}
						</span>
						<p>{{ _lanq('lara-admin::2fa.message.is_enabled') }}</p>
					@else
						<span style="color: #d81b60; font-size: 16px; font-weight: bold;">
							{{ strtoupper(_lanq('lara-admin::2fa.value.status_disabled')) }}
						</span>
					@endif
				</div>
				<div class="col-12 col-md-2 text-md-end">
					{{ ucfirst(_lanq('lara-admin::2fa.column.action')) }} :
				</div>
				<div class="col-12 col-md-2">
					@if($data->object->hasTwoFactor())
						{{ html()->input('submit', '_deactivate_2fa', _lanq('lara-admin::2fa.button.deactivate_2fa_now'))->class('btn btn-sm btn-danger') }}
					@else
						{{ html()->input('submit', '_activate_2fa', _lanq('lara-admin::2fa.button.activate_2fa_now'))->class('btn btn-sm btn-danger') }}
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="fields">
		{{ _lanq('lara-admin::2fa.boxtitle.2fa') }} :
	</x-boxheader>

	<div id="kt_card_collapsible_fields" class="collapse show">
		<div class="box-body">

			<x-showrow>
				<x-slot name="label">
					{{ ucfirst(_lanq('lara-admin::2fa.column.qrcode')) }} :
				</x-slot>
				@if(!$data->object->hasTwoFactor())
					{{ html()->hidden('_new_secret_key', $data->newSecretKey) }}
				@endif
				<img src="data:image/png;base64, <?php echo $data->qrCodeImage; ?> "
				     style="border: solid 1px #788caa;"/>
			</x-showrow>

			<x-showrow>
				<x-slot name="label">
					Authenticator App :
				</x-slot>
				<a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank"
				   class="me-8">
					<img src="{{ asset('assets/admin/img/2fa/apple-app-store@2x.png') }}" width="134"
					     alt="Apple App Store"/>
				</a>
				<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=nl"
				   target="_blank" alt="Android" title="Android" class="me-8">
					<img src="{{ asset('assets/admin/img/2fa/google-play@2x.png') }}" width="134"
					     alt="Apple App Store"/>
				</a>
			</x-showrow>

			@if($data->object->hasTwoFactor())
				<x-showrow>
					<x-slot name="label">
						{{ ucfirst(_lanq('lara-admin::2fa.column.recovery_codes')) }} :
					</x-slot>
					<span style="font-family: 'Courier New', serif">
						@foreach($data->object->recoveryCodes as $recoveryCode)
							{{ $recoveryCode }}<br>
						@endforeach
					</span>
				</x-showrow>
			@endif

		</div>
	</div>

</div>















