<div class="row my-8">
	<div class="col-4  offset-sm-4">
		@if($entity->hasTags())
			@include('lara-admin::_partials.filterbytaxonomy')
		@elseif($entity->hasGroups())
			@include('lara-admin::_partials.filterbygroup')
		@else
			&nbsp;
		@endif
	</div>
</div>

<div class="row">
	<div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">

		<table class="table table-lara-sortable">
			<tbody class="sortable" data-entityname="{{ $entity->getEntityKey() }}">
				@foreach( $data->objects as $obj )
					<tr class="sortable-row" data-itemId="{{ $obj->id }}">
						<td class="sortable-handle ps-3">
							<i class="fal fa-sort"></i>
						</td>
						<td class="sortable-handle">
							{{ str_limit($obj->title, 50) }}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

	</div>
</div>

