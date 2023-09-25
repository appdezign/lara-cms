<div class="box box-default">

	<x-boxheader cstate="active" collapseid="status">
		{{ _lanq('lara-admin::default.boxtitle.status') }}
	</x-boxheader>

	<div id="kt_card_collapsible_status" class="collapse show">
		<div class="box-body">

			<div class="row">
				<div class="col-12 col-sm-6">
					&nbsp;
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
								{{ html()->radio('publish', old('publish', $data->object->publish) == 1, 1)->id('publish_1')->class('form-check-input') }}
							</div>
						</div>
					</div>

					<div class="row form-group">
						<div class="col-8 text-end">
							{{ html()->label(_lanq('lara-admin::default.value.status_draft'), 'publish_0') }}
						</div>
						<div class="col-2 text-right">
							<div class="form-check">
								{{ html()->radio('publish', old('publish', $data->object->publish) == 0, 0)->id('publish_0')->class('form-check-input') }}
							</div>
						</div>
					</div>

				</div>
			</div>

		</div>
	</div>
</div>
