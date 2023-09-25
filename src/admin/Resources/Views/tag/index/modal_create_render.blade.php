<option value="{{ $node->id }}">
	@if($node->depth > 1)
		@for($i = 1; $i < $node->depth; $i++)
			&nbsp;&dash;
		@endfor
		&nbsp;
	@endif
	{{ $node->title }}
</option>

@if(!$node->isLeaf())
	@if(!empty($node->children))
		@foreach ($node->children as $node)
			@include('lara-admin::tag.index.modal_create_render', $node)
		@endforeach
	@endif
@endif

