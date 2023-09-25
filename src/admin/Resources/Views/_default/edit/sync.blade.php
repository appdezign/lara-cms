@if(config('lara.has_remote_sync') && Auth::user()->isAn('administrator'))

	<div class="box box-default">

		<x-boxheader cstate="@if(empty($data->object->sync)) collapsed @else active @endif" collapseid="sync">
			{{ _lanq('lara-admin::default.boxtitle.sync') }}
		</x-boxheader>

		<div id="kt_card_collapsible_sync" class="collapse @if(!empty($data->object->sync)) show @endif">
			<div class="box-body">

				@if(empty($data->object->sync))
					<div class="row mb-5">
						<div class="col-5">
							remote url:
						</div>
						<div class="col-2">
							remote suffix:
						</div>
						<div class="col-2">
							entity key:
						</div>
						<div class="col-3">
							slug:
						</div>
					</div>
					<div class="row">

						<div class="col-5">
							{{ html()->text('_new_remote_url', null, ['class' => 'form-control']) }}
						</div>
						<div class="col-2">
							{{ html()->hidden('_new_remote_suffix', '/'.$data->object->language.'/api/') }}
							{{ html()->text('_new_remote_suffix', '/'.$data->object->language.'/api/')->class('form-control')->disabled() }}
						</div>
						<div class="col-2">
							{{ html()->hidden('_new_remote_ekey', $entity->getEntityKey()) }}
							{{ html()->text('_new_remote_ekey', $entity->getEntityKey())->class('form-control')->disabled() }}
						</div>
						<div class="col-3">
							{{ html()->hidden('_new_remote_slug', $data->object->slug) }}
							{{ html()->text('_new_remote_slug', $data->object->slug)->class('form-control')->disabled() }}
						</div>
					</div>

				@else

					<div class="row mb-5">
						<div class="col-4">
							remote url:
						</div>
						<div class="col-2">
							remote suffix:
						</div>
						<div class="col-2">
							entity key:
						</div>
						<div class="col-2">
							slug:
						</div>
						<div class="col-2">
							DELETE
						</div>
					</div>
					<div class="row">

						<div class="col-4">
							{{ html()->text('_remote_url', $data->object->sync->remote_url)->class('form-control') }}
						</div>
						<div class="col-2">
							{{ html()->hidden('_remote_suffix', '/'.$data->object->language.'/api/') }}
							{{ html()->text('_remote_suffix', $data->object->sync->remote_suffix)->class('form-control')->disabled() }}
						</div>
						<div class="col-2">
							{{ html()->text('_remote_ekey', $entity->getEntityKey())->class('form-control')->disabled() }}
						</div>
						<div class="col-2">
							{{ html()->text('_remote_slug', $data->object->slug)->class('form-control')->disabled() }}
						</div>
						<div class="col-2">
							{{ html()->text('_remote_delete', null)->class('form-control') }}
						</div>
					</div>
				@endif

			</div>
		</div>
	</div>

@else

	@if(!empty($data->object->sync))

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="sync">
				{{ _lanq('lara-admin::default.boxtitle.sync') }}
			</x-boxheader>

			<div id="kt_card_collapsible_sync" class="collapse show">
				<div class="box-body">

					<x-showrow>
						<x-slot name="label">
							{{ _lanq('lara-' . $entity->getModule().'::sync.column.is_synced_to') }}
						</x-slot>

						{{ $data->object->sync->remote_url }}{{ $data->object->sync->remote_suffix }}{{ $entity->getEntityKey() }}
						/{{ $data->object->slug }}
					</x-showrow>

				</div>
			</div>
		</div>

	@endif

@endif

