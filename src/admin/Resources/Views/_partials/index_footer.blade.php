@if($data->params->paginate == true)
	{{ ($data->objects->currentPage() - 1) * $data->objects->perPage() + 1 }}
	- {{ $data->objects->currentPage() * $data->objects->perPage() > $data->objects->total() ? $data->objects->total() : $data->objects->currentPage() * $data->objects->perPage() }}
	/ {{ $data->objects->total() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
@else
	@if($data->objects instanceof \Illuminate\Pagination\LengthAwarePaginator )
		{{ $data->objects->total() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
	@else
		{{ $data->objects->count() }} {{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.entity_plural') }}
	@endif
@endif

@if($data->params->paginate == true)

	<div class="pagination-page-select">

		<select name="display" class="form-select form-select-sm" data-control="select2" data-hide-search="true"
		        onchange="location = this.options[this.selectedIndex].value;">
			<option value="{{ Request::url() }}?page={{ $data->objects->currentPage() }}&perpage=10"
			        @if($data->objects->perPage() == 10) selected @endif>10
			</option>
			<option value="{{ Request::url() }}?page={{ $data->objects->currentPage() }}&perpage=15"
			        @if($data->objects->perPage() == 15) selected @endif>15
			</option>
			<option value="{{ Request::url() }}?page={{ $data->objects->currentPage() }}&perpage=20"
			        @if($data->objects->perPage() == 20) selected @endif>20
			</option>
			<option value="{{ Request::url() }}?page={{ $data->objects->currentPage() }}&perpage=25"
			        @if($data->objects->perPage() == 25) selected @endif>25
			</option>
			<option value="{{ Request::url() }}?page={{ $data->objects->currentPage() }}&perpage=all"
			        @if(!is_numeric($data->objects->perPage())) selected @endif>{{ _lanq('lara-admin::default.paginate.all') }}
			</option>
		</select>
	</div>

@else

	@if($data->filters->filter == false && $data->filters->search == false)

		<div class="pagination-page-select">
			<select name="display" class="form-select form-select-sm"
			        onchange="location = this.options[this.selectedIndex].value;">
				<option value="{{ Request::url() }}?page=1&perpage=10">10</option>
				<option value="{{ Request::url() }}?page=1&perpage=15">15</option>
				<option value="{{ Request::url() }}?page=1&perpage=20">20</option>
				<option value="{{ Request::url() }}?page=1&perpage=25">25</option>
				<option value="{{ Request::url() }}?page=1&perpage=all"
				        selected>{{ _lanq('lara-admin::default.paginate.all') }}</option>
			</select>
		</div>

	@endif

@endif