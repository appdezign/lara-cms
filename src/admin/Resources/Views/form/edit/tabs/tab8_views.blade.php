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
					&nbsp;
				</div>
				<div class="col-1 crud-header-col">
					&nbsp;
				</div>
				<div class="col-1 crud-header-col text-center">
					&nbsp;
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

					<div class="col-2 crud-col">
						{{ html()->hidden('_vtag_'.$entview['id'], 'none') }}
					</div>
					<div class="col-1 crud-col">
						{{ html()->hidden('_vpaginate_'.$entview['id'], 0) }}
					</div>
					<div class="col-1 crud-col text-center">
						{{ html()->hidden('_vprevnext_'.$entview['id'], 0) }}
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


@if(sizeof($data->entityViews) == 0)
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
						{{ _lanq('lara-admin::entity.column.view_friendly_name') }}
					</div>
				</div>

				<div class="row crud-row form-group">
					<div class="col-3 crud-col">
						{{ html()->select('_new_view_method', [null=>'- Select -'] + $data->availableMethods, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
					</div>
					<div class="col-3 crud-col">
						{{ html()->text('_new_view_title', null)->class('form-control') }}
					</div>

				</div>

			</div>
		</div>

	</div>

@endif