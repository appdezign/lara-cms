<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">

	<li class="nav-item">
		<button class="nav-link js-first-tab" type="button" role="tab" data-bs-toggle="tab"
		        data-bs-target="#tab_0">
			{{ _lanq('lara-admin::entity.tab.info') }}
		</button>
	</li>

	@if($data->object->egroup->group_has_columns == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_1">
				{{ _lanq('lara-admin::entity.tab.columns') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_objectrelations == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_2">
				{{ _lanq('lara-admin::entity.tab.objectrelations') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_filters == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_3">
				{{ _lanq('lara-admin::entity.tab.filters') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_panels == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_4">
				{{ _lanq('lara-admin::entity.tab.panels') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_media == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_5">
				{{ _lanq('lara-admin::entity.tab.media') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_customcolumns == 1)
		@if($data->object->columns->has_fields == 1)
			<li class="nav-item">
				<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
				        data-bs-target="#tab_6">
					{{ _lanq('lara-admin::entity.tab.customcolumns') }}
				</button>
			</li>
		@endif
	@endif

	@if($data->object->egroup->group_has_relations == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_7">
				{{ _lanq('lara-admin::entity.tab.relations') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_views == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_8">
				{{ _lanq('lara-admin::entity.tab.views') }}
			</button>
		</li>
	@endif

	@if($data->object->egroup->group_has_sortable == 1)
		<li class="nav-item">
			<button class="nav-link" type="button" role="tab" data-bs-toggle="tab"
			        data-bs-target="#tab_9">
				{{ _lanq('lara-admin::entity.tab.sortable') }}
			</button>
		</li>
	@endif

</ul>

<div class="tab-content" id="myTabContent">

	<div class="tab-pane fade" id="tab_0" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab0_info', 'lara-admin::entity.edit.tabs.tab0_info'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_1" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab1_columns', 'lara-admin::entity.edit.tabs.tab1_columns'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_2" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab2_objectrelations', 'lara-admin::entity.edit.tabs.tab2_objectrelations'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_3" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab3_filters', 'lara-admin::entity.edit.tabs.tab3_filters'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_4" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab4_panels', 'lara-admin::entity.edit.tabs.tab4_panels'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_5" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab5_media', 'lara-admin::entity.edit.tabs.tab5_media'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_6" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab6_customcolumns', 'lara-admin::entity.edit.tabs.tab6_customcolumns'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_7" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab7_relations', 'lara-admin::entity.edit.tabs.tab7_relations'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_8" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab8_views', 'lara-admin::entity.edit.tabs.tab8_views'])
	</div>
	<!-- /.tab-pane -->

	<div class="tab-pane fade" id="tab_9" role="tabpanel">
		@includeFirst(['lara-admin::form.edit.tabs.tab9_sortable', 'lara-admin::entity.edit.tabs.tab9_sortable'])
	</div>
	<!-- /.tab-pane -->

</div>
<!-- /.tab-content -->


