@if($entity->hasStatus())

	@if($entity->getShowStatus())

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="status">
				{{ _lanq('lara-admin::default.boxtitle.status') }}
			</x-boxheader>

			<div id="kt_card_collapsible_status" class="collapse show">
				<div class="box-body">

					<div class="row">
						<div class="col-12 col-sm-6">

							<div class="row form-group">
								<div class="col-12 col-md-4">
									{{ html()->label(_lanq('lara-admin::default.column.publish_from').':', 'publish_from') }}
								</div>
								<div class="col-12 col-md-8">

									<div id="dtp-publish-from" class="date-flat-pickr">
										{{ html()->text('publish_from', old('publish_from', $data->object->publish_from))->class('form-control')->data('input') }}
										<a class="flat-pickr-button" title="toggle" data-toggle>
											<i class="fal fa-calendar-alt"></i>
										</a>
									</div>

								</div>
							</div>

						</div>

						<div class="col-12 d-block d-md-none">
							<hr>
						</div>

						<div class="col-12 col-sm-6">
							<div class="row form-group">
								<div class="col-8 text-end">
									{{ html()->label(_lanq('lara-admin::default.value.status_published'), 'publish_1') }}
								</div>
								<div class="col-2">
									<div class="form-check">
										{{ html()->radio('publish', old('publish') == 1, 1)->class('form-check-input')->id('publish_1') }}
									</div>
								</div>
							</div>

							<div class="row form-group">
								<div class="col-8 text-end">
									{{ html()->label(_lanq('lara-admin::default.value.status_draft'), 'publish_0') }}
								</div>
								<div class="col-2 text-right">
									<div class="form-check">
										{{ html()->radio('publish', old('publish') == 0, 0)->class('form-check-input')->id('publish_0') }}
									</div>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>
		</div>

	@else

		{{ html()->hidden('publish', 1) }}
		{{ html()->hidden('publish_from', Date::parse(\Carbon\Carbon::now())->format('Y-m-d H:i:s')) }}

	@endif

@else

	{{ html()->hidden('publish', 1) }}
	{{ html()->hidden('publish_from', Date::parse(\Carbon\Carbon::now())->format('Y-m-d H:i:s')) }}

@endif