<li id="list_{{ $node->id }}">
	@if($node->depth == 0)
		<div class="reorder-home-muted">
			<span class="disclose"><span></span></span>
			<i class="fa fa-home fa-lg"></i>
		</div>
	@else
		<div>
			<span class="disclose"><span></span></span>{{ $node->title }}
		</div>

	@endif

	@if (!$node->isLeaf())
		<ol>
			@if(!empty($node->children))
				@foreach ($node->children as $node)
					@include('lara-admin::tag.reorder.content_render', $node)
				@endforeach
			@endif
		</ol>
	@endif
</li>






