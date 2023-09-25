@if($entity->hasStatus())

	@if($entity->getShowStatus())

		<div class="box box-default @if($data->status->publish) status-safe @else status-warning @endif ">

			<x-boxheader cstate="active" collapseid="status">
				{{ _lanq('lara-admin::default.boxtitle.status') }}: {{ $data->status->message }}
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

							@if($entity->hasExpiration())

								@if(empty($data->object->publish_to))

									<div class="row form-group">
										<div class="col-8 col-sm-4 label_publish_to">
											{{ html()->label(_lanq('lara-admin::default.column.publish_to').':', 'publish_to') }}
										</div>
										<div class="col-2 col-sm-8 field_publish_to">
											<div class="mb-4">
												<div class="form-check">
													{{ html()->checkbox('_show_expiration', null, 1)->class('form-check-input')->id('show_expiration') }}
												</div>
											</div>
										</div>
										<div class="col-12 col-sm-8 offset-sm-4">
											<div class="expiration_date mb-5" style="display:none">

												<div id="dtp-publish-to" class="date-flat-pickr">
													{{ html()->text('publish_to', old('publish_to', $data->object->publish_to))->class('form-control')->data('input') }}
													<a class="flat-pickr-button" title="toggle" data-toggle>
														<i class="fal fa-calendar-alt"></i>
													</a>
												</div>

											</div>
										</div>
									</div>

								@else
									<div class="row form-group">
										<div class="col-8 col-sm-4 label_publish_to">
											{{ html()->label(_lanq('lara-admin::default.column.publish_to').':', 'publish_to') }}
										</div>
										<div class="col-2 col-sm-8 field_publish_to">

											<div id="dtp-publish-to" class="date-flat-pickr">
												{{ html()->text('publish_to', old('publish_to', $data->object->publish_to))->class('form-control')->data('input') }}
												<a class="flat-pickr-button" title="toggle" data-toggle>
													<i class="fal fa-calendar-alt"></i>
												</a>
											</div>

										</div>
									</div>
								@endif

							@endif

							@if($entity->hasHideinlist())
								<div class="row form-group">
									<div class="col-8 col-sm-4 label_publish_hide">
										{{ html()->label(_lanq('lara-admin::default.column.publish_hide').':', 'publish_hide') }}
									</div>
									<div class="col-2 col-sm-8 field_publish_hide">
										<div class="form-check">
											@if($data->object->publish == 1)
												{{ html()->hidden('publish_hide', 0) }}
												{{ html()->checkbox('publish_hide', old('publish_hide', $data->object->publish_hide), 1)->class('form-check-input') }}
											@else
												{{ html()->hidden('publish_hide', $data->object->publish_hide) }}
												{{ html()->checkbox('publish_hide', old('publish_hide', $data->object->publish_hide), 1)->class('form-check-input')->disabled() }}
											@endif
										</div>
									</div>
								</div>
							@endif

							@if($entity->hasApp())
								<div class="row form-group">
									<div class="col-8 col-sm-4 label_show_in_app">
										{{ html()->label(_lanq('lara-admin::default.column.show_in_app').':', 'show_in_app') }}
									</div>
									<div class="col-2 col-sm-8 field_show_in_app">
										<div class="form-check">
											{{ html()->hidden('show_in_app', 0) }}
											{{ html()->checkbox('show_in_app', old('show_in_app', $data->object->show_in_app), 1)->class('form-check-input') }}
										</div>
									</div>
								</div>
							@endif

						</div>

						<div class="col-12 d-block d-sm-none">
							<hr>
						</div>

						<div class="col-12 col-sm-6">
							<div class="row form-group">
								<div class="col-8 text-end">
									{{ html()->label(_lanq('lara-admin::default.value.status_published'), 'publish_1') }}
								</div>
								<div class="col-2">
									<div class="form-check">
										{{ html()->radio('publish', old('publish', $data->object->publish) == 1, 1)->class('form-check-input')->id('publish_1') }}
									</div>
								</div>
							</div>

							<div class="row form-group">
								<div class="col-8 text-end">
									{{ html()->label(_lanq('lara-admin::default.value.status_draft'), 'publish_0') }}
								</div>
								<div class="col-2 text-right">
									<div class="form-check">
										{{ html()->radio('publish', old('publish', $data->object->publish) == 0, 0)->class('form-check-input')->id('publish_0') }}
									</div>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>
		</div>

	@else

		{{ html()->hidden('publish', $data->object->publish) }}
		{{ html()->hidden('publish_from', $data->object->publish_from) }}

		@if($entity->hasHideinlist())
			{{ html()->hidden('publish_hide', $data->object->publish_hide) }}
		@endif

		@if($entity->hasExpiration())
			{{ html()->hidden('publish_to', $data->object->publish_to) }}
		@endif

		@if($entity->hasApp())
			{{ html()->hidden('show_in_app', $data->object->show_in_app) }}
		@endif

	@endif

@endif
