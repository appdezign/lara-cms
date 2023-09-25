@if($node->depth > 0)
	<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['tag' => $node->id]) }}"
	        @if($node->id == $data->filters->tag) selected @endif >

		@if($node->depth > 1)
			@for($i = 1; $i < $node->depth; $i++)
				&nbsp;&dash;
			@endfor
			&nbsp;
		@endif

		{{ $node->title }}
	</option>
@endif

@if(!$node->isLeaf())
	@foreach($node->children as $node)
		@include('lara-admin::_partials.filterbytaxonomy_render', $node)
	@endforeach
@endif



