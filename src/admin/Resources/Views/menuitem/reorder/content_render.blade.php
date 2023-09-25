<li id="list_{{ $node->id }}">
	<div><span class="disclose"><span></span></span>{{ $node->title }}</div>

	@if (!$node->isLeaf())
		<ol>
			@if(!empty($node->children))
				@foreach ($node->children as $node)
					@include('lara-admin::menuitem.reorder.content_render', $node)
				@endforeach
			@endif
		</ol>
	@endif
</li>






