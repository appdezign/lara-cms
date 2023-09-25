<?php $padding = ($node->depth > 0) ? ($node->depth - 1) * 30 : 0; ?>
<?php $nodeclass = $node->isLeaf() ? 'isLeaf' : 'hasChildren'; ?>

<li class="{{ $nodeclass }}">

	<div class="row">

		<div class="menu-title col-1 text-right">
			@if ($node->depth > 0)
				@if (config('lara-admin.taxonomy.disable_root_parents') && !$node->isLeaf() && $node->depth == 1)
					<div class="form-check">
						{{ html()->checkbox('_tags_array[]', (in_array($node->id, $data->objecttags)), $node->id)->class('form-check-input')->disabled() }}
					</div>
				@else
					<div class="form-check">
						{{ html()->checkbox('_tags_array[]', (in_array($node->id, $data->objecttags)), $node->id)->class('form-check-input') }}
					</div>
				@endif
			@endif
		</div>

		<div class="menu-title col-11" style="padding-left: {{ $padding }}px">

			@if($node->depth > 0)
				<div class="child-icon">
					<i class="fal fa-chevron-left"></i>
				</div>
				<i class="fas fa-folder-open fa-lg"></i>
			@else
				<i class="fas fa-home fa-lg"></i>
			@endif


			@if ($node->depth > 0)
				{{ $node->title }}
			@else
				{{ $entity->getTitle() }}
			@endif

		</div>

	</div>

	@if (!$node->isLeaf())
		<ul class="children">
			@if(!empty($node->children))
				@foreach ($node->children as $node)
					@include('lara-admin::_partials.tags_render', $node)
				@endforeach
			@endif
		</ul>
	@endif

</li>

