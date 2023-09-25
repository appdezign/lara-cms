<?php
$paginate_array = array();
$paginate_array[0] = '- -';
for ($i = 1; $i <= 100; $i++) {
	$paginate_array[$i] = $i;
}

?>

@include('lara-admin::_partials.count')

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="viewlist">
		{{ _lanq('lara-admin::entity.boxtitle.views') }}
	</x-boxheader>

	<div id="kt_card_collapsible_viewlist" class="collapse show">
		<div class="box-body pb-10">

			<div class="row crud-header-row">
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_method') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_friendly_name') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_type') }}
				</div>
				<div class="col-2 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_tag') }}
				</div>
				<div class="col-1 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_paginate') }}
				</div>
				<div class="col-1 crud-header-col text-center">
					{{ _lanq('lara-admin::entity.column.view_prevnext') }}
				</div>
				<div class="col-2 crud-header-col">DELETE</div>
			</div>

			@foreach($data->entityViews as $entview)

				<div class="row crud-row edit-crud-row">
					<div class="col-2 crud-col">
						{{ html()->text('_vmethod_'.$entview['id'], old('_vmethod_'.$entview['id'], $entview['method']))->class('form-control')->disabled() }}
					</div>

					<div class="col-2 crud-col">
						{{ html()->text('_vtitle_'.$entview['id'], old('_vtitle_'.$entview['id'], $entview['title']))->class('form-control') }}
					</div>

					<div class="col-2 crud-col">
						{{ html()->select('_vtype_'.$entview['id'], $data->entityViewTypes, old('_vtype_'.$entview['id'], $entview['type']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>

					@if($entview['type'] == '_single' || $entview['type'] == '_form')
						<div class="col-2 crud-col">
							{{ html()->hidden('_vtag_'.$entview['id'], 'none') }}
							{{ html()->select('_vtag_'.$entview['id'], $data->entityViewShowTags, old('_vtag_'.$entview['id'], $entview['showtags']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
						</div>
					@else
						<div class="col-2 crud-col">
							{{ html()->select('_vtag_'.$entview['id'], $data->entityViewShowTags, old('_vtag_'.$entview['id'], $entview['showtags']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
						</div>
					@endif

					<div class="col-1 crud-col">
						@if(substr($entview['type'],0,1) == '_' || substr($entview['showtags'],0,1) == '_')
							{{ html()->hidden('_vpaginate_'.$entview['id'], 0) }}
							{{ html()->select('_vpaginate_'.$entview['id'], $paginate_array, old('_vpaginate_'.$entview['id'], $entview['paginate']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->disabled() }}
						@else
							{{ html()->select('_vpaginate_'.$entview['id'], $paginate_array, old('_vpaginate_'.$entview['id'], $entview['paginate']))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
						@endif
					</div>
					<div class="col-1 crud-col text-center">
						<div class="form-check">
							@if(substr($entview['type'],0,1) == '_')
								{{ html()->hidden('_vprevnext_'.$entview['id'], 0) }}
								{{ html()->checkbox('_vprevnext_'.$entview['id'], old('_vprevnext_'.$entview['id'], $entview['prevnext']), 1)->class('form-check-input') }}
							@else
								{{ html()->hidden('_vprevnext_'.$entview['id'], 0) }}
								{{ html()->checkbox('_vprevnext_'.$entview['id'], old('_vprevnext_'.$entview['id'], $entview['prevnext']), 1)->class('form-check-input')->disabled() }}
							@endif
						</div>
					</div>
					<div class="col-2 crud-col">
						{{ html()->text('_vdelete_'.$entview['id'], old('_vdelete_'.$entview['id']))->class('form-control') }}
					</div>

					{{ html()->hidden('_vpublish_'.$entview['id'], 1) }}

				</div>
			@endforeach

		</div>

	</div>
</div>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="viewadd">
		{{ _lanq('lara-admin::entity.boxtitle.add_view') }}
	</x-boxheader>

	<div id="kt_card_collapsible_viewadd" class="collapse show">
		<div class="box-body pb-10">

			<div class="row crud-header-row">
				<div class="col-3 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_method') }}
				</div>
				<div class="col-3 crud-header-col">
					&nbsp;
				</div>
				<div class="col-3 crud-header-col">
					{{ _lanq('lara-admin::entity.column.view_friendly_name') }}
				</div>
			</div>

			<div class="row crud-row form-group">
				<div class="col-3 crud-col">
					{{ html()->select('_new_view_method', [null=>'- Select -'] + $data->availableMethods, null)->id('view_method_select')->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</div>
				<div class="col-3 crud-col">
					<div id="new_custom_method" style="display:none">
						{{ html()->text('_new_custom_method', null)->class('form-control') }}
					</div>
				</div>
				<div class="col-3 crud-col">
					{{ html()->text('_new_view_title', null)->class('form-control') }}
				</div>

			</div>

		</div>
	</div>

</div>
