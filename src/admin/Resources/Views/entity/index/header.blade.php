<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">

			{{-- custom group filter --}}
			<select name="tag" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter"
			        data-hide-search="true" onchange="if (this.value) window.location.href=this.value">

				<option value="{{ route('admin.'.$entity->entity_key.'.index', ['egroup' => '']) }}">
					@if($data->filtergroup != '')
						{{ _lanq('lara-admin::default.form.show_all') }}
					@else
						{{ _lanq('lara-admin::default.form.filter') }}
					@endif
				</option>

				@foreach($data->entityGroups as $group_id => $groupname)
					<option value="{{ route('admin.'.$entity->entity_key.'.index', ['egroup' => $group_id]) }}"
					        @if($group_id == $data->filtergroup) selected @endif >
						{{ $groupname }}
					</option>
				@endforeach

			</select>

		</div>
	</div>

	<div class="col-5">
		<div class="search">
			&nbsp;
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">

			<a href="{{ route('admin.'.$entity->entity_key.'.create') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="{{ _lanq('lara-admin::default.button.add') }} {{ _lanq('lara-admin::entity.entity.entity_single') }}">
				<i class="fal fa-plus"></i>
			</a>

			<a href="{{ route('admin.'.$entity->entity_key.'.export') }}"
			   title="Export all entity content to iseed files"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary">
				<i class="far fa-file-export"></i>
			</a>

		</div>
	</div>

</div>

{{-- BATCH--}}
<div class="row index-batch d-none">
	<div class="col-1 text-center batch">
		<div class="select-all-icon">
			<i class="bi bi-arrow-90deg-right"></i>
		</div>
	</div>
	<div class="col-11 batch">
		{{ html()->input('submit', 'saveall', 'check integrity')->class('btn btn-sm btn-danger') }}
	</div>
</div>

