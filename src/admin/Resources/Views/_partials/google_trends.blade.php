<!-- Modal Edit -->
<div class="modal fade" id="googleTrendsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Google Trends</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.seo_focus').':', '_seo_focus') }}
					</x-slot>

					<a class="btn btn-sm btn-primary float-end" id="getGoogleTrends">get trends</a>

					{{ html()->text('_seo_focus', $data->object->seo_focus)->class('form-control')->style(['width' => '80%']) }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.seo_compare').':', '_secondary') }}
					</x-slot>

					{{ html()->text('_secondary', null)->class('form-control')->style(['width' => '80%']) }}

				</x-formrow>

				<div class="row mb-5">
					<div class="col">
						<div id="googleTrendsTime"></div>
					</div>
				</div>

				<div class="row mb-5">
					<div class="col">
						<div id="googleTrendsGeo"></div>
					</div>
					<div class="col-6">
						<div id="googleTrendsGeo1"></div>
					</div>
					<div class="col-6">
						<div id="googleTrendsGeo2"></div>
					</div>
				</div>

				<div class="row mb-5">
					<div class="col">
						<div id="googleTrendsRel"></div>
					</div>
					<div class="col-6">
						<div id="googleTrendsRel1"></div>
					</div>
					<div class="col-6">
						<div id="googleTrendsRel2"></div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ _lanq('lara-admin::menuitem.button.close') }}</button>
			</div>
		</div>
	</div>
</div>