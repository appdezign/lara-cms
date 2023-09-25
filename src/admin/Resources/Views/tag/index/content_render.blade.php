<?php $padding = ($node->depth > 0) ? ($node->depth - 1) * 30 : 0; ?>
<?php $nodeclass = $node->isLeaf() ? 'isLeaf' : 'hasChildren'; ?>
<?php $rowstyle = ($node->publish == 1) ? 'published' : 'draft'; ?>

<li class="{{ $nodeclass }}">

	<div class="row">

		<div class="menu-title col-12 col-sm-8 {{ $rowstyle }}" style="padding-left: {{ $padding }}px">

			@if($node->depth > 0)
				<div class="child-icon">
					<i class="fal fa-chevron-left"></i>
				</div>
				<i class="fas fa-folder-open fa-lg {{ $rowstyle }}"></i>
			@else
				<i class="fas fa-home fa-lg {{ $rowstyle }}"></i>
			@endif

			@if ($node->depth > 0)
				{{ str_limit($node->title, 60, ' ..') }}
				@if($entity->hasImages() && $node->media->count())
					<i class="fal fa-file-image d-none d-sm-block float-end"></i>
				@endif
			@else
				{{ $data->related->getTitle() }}
			@endif
		</div>

		<div class="col-2 offset-4 col-sm-1 offset-sm-0 action-icons">
			@if($node->locked_by_admin == 1 && $node->depth > 0)
				<div class="edit-locked">
					<i class="las la-lock" style></i>
				</div>
			@else
				&nbsp;
			@endif
		</div>

		<div class="col-2 col-sm-1 action-icons">
			@if($node->depth > 0)
				@if (!config('lara-admin.taxonomy.disable_root_parents') || $node->isLeaf() || $node->depth > 1)
					<a href="{{ route('admin.tag.taggable', ['id' => $node->id, 'entity' => $data->related->getEntityKey()]) }}" >
						<i class="fal fa-list-alt"></i>
					</a>
				@else
					<div class="action-icon-disabled text-muted">
						<i class="fal fa-list-alt"></i>
					</div>
				@endif
			@endif
		</div>

		<div class="col-2 col-sm-1 action-icons">
			@if($node->depth > 0)
				@if($node->locked_by_admin == 0  || Auth::user()->isAn('administrator'))
					@can('update', $node)
						<a href="{{ route('admin.tag.edit', ['id' => $node->id, 'entity' => $data->related->getEntityKey()]) }}" >
							<i class="las la-edit"></i>
						</a>
					@else
						<div class="action-icon-disabled text-muted">
							<i class="las la-edit"></i>
						</div>
					@endif
				@else
					<div class="action-icon-disabled text-muted">
						<i class="las la-edit"></i>
					</div>
				@endif
			@endif
		</div>

		<div class="col-2 col-sm-1 action-icons text-center">
			@if($node->depth > 0)
				@if($node->locked_by_admin == 0 || Auth::user()->isAn('administrator'))
					@if($node->isLeaf() || (!$node->isLeaf() && sizeof($node->children) == 0))
						@can('delete', $node)
							<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.destroy', ['id' => $node->id]) }}"
							   data-token="{{ csrf_token() }}"
							   data-confirm="{{ _lanq('lara-admin::default.message.confirm') }}" data-method="delete">
								<i class="las la-trash"></i>
							</a>
						@else
							<div class="action-icon-disabled text-muted">
								<i class="las la-trash"></i>
							</div>
						@endif
					@else
						<div class="action-icon-disabled text-muted">
							<i class="las la-trash"></i>
						</div>
					@endif

				@else
					<div class="action-icon-disabled text-muted">
						<i class="las la-trash"></i>
					</div>
				@endif

			@endif

		</div>

	</div>

	@if (!$node->isLeaf())
		<ul class="children">
			@if(!empty($node->children))
				@foreach ($node->children as $node)
					@include('lara-admin::tag.index.content_render', $node)
				@endforeach
			@endif
		</ul>
	@endif

</li>

