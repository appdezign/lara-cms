<?php use Lara\Common\Models\Tag;

$navlink = ($node->type == 'url') ? $node->url : url($clanguage . '/' . $node->route);

$padding = ($node->depth > 0) ? ($node->depth - 1) * 30 : 0;
$nodeclass = $node->isLeaf() ? 'isLeaf' : 'hasChildren';
$rowstyle = ($node->publish == 1) ? 'published' : 'draft';

if ($node->type == 'entity') {
	if ($node->tag_id) {
		$tag = Tag::find($node->tag_id);
		if ($tag) {
			$tagTitle = $tag->title;
		}
	} else {
		$tagTitle = null;
	}
} else {
	$tagTitle = null;
}
?>

<li class="{{ $nodeclass }}">

	<div class="row">

		<div class="menu-title col-8 col-sm-5 {{ $rowstyle }}" style="padding-left: {{ $padding }}px">

			@if ($node->depth > 0)
				<div class="child-icon">
					<i class="fal fa-chevron-left"></i>
				</div>
			@endif

			<a href="{{ $navlink }}" target="_blank" class="title">
				@if ($node->depth > 0)
					@if ($node->type == 'parent')
						<i class="fas fa-folder-open"></i>
					@elseif ($node->type == 'entity')
						<i class="fal fa-cube"></i>
					@elseif ($node->type == 'page')
						<i class="fal fa-file-text"></i>
					@else
						<i class="fal fa-file-text"></i>
					@endif
				@else
					<i class="fas fa-home"></i>
				@endif
				{{ $node->title }}
				@if($node->slug_lock == 1)
					<i class="las la-lock"></i>
				@endif
			</a>

			@if($node->depth == 0 || $node->type == 'parent')
				@if($node->children()->count() < config('lara-admin.menu.max_children'))
					<a class="open-create-menu-modal"
					   data-bs-toggle="modal"
					   data-bs-target="#menuCreateModal"
					   data-parentid="{{ $node->id }}">

						<i class="fal fa-plus"></i>
					</a>
				@else
					<div class="d-inline py-1 px-3">
						<i class="fal fa-plus" style="color:#ddd;"></i>
					</div>
				@endif
			@endif
		</div>

		<div class="menu-edit d-none d-sm-block col-sm-1 {{ $rowstyle }}">
			<div class="edit-locked">
				@if($node->route_has_auth == 1)
					<i class="far fa-sign-in float-end color-danger"></i>
				@endif
				@if($node->locked_by_admin == 1)
					<i class="las la-lock float-end"></i>
				@endif
			</div>
		</div>

		<div class="menu-model d-none d-sm-block col-sm-1 text-center {{ $rowstyle }}">
			{{ _lanq('lara-' . $entity->getModule().'::menuitem.value.type_' . $node->type) }}
		</div>

		<div class="menu-model d-none d-sm-block col-sm-3 {{ $rowstyle }}">

			@if ($node->type == 'entity')
				{{ $node->entity->entity_key }}
				@if($tagTitle)
					&nbsp; &rsaquo; {{ $tag->title }}
				@endif
			@elseif ($node->type == 'form')
				{{ $node->entity->entity_key }}
			@elseif ($node->type == 'page')
				<?php $pg = \Lara\Common\Models\Page::where('id', $node->object_id)->first(); ?>
				{{ str_limit($pg->title, 25, ' ...') }}
			@elseif ($node->type == 'url')
				<a href="{{ $node->url }}" target="_blank">{{ $node->url }}</a>
			@elseif ($node->type == 'parent')
				&nbsp;
			@endif

		</div>

		<div class="menu-edit col-2 col-sm-1 {{ $rowstyle }}">
			@if($node->locked_by_admin == 0 || Auth::user()->isAn('administrator'))
				<a class="open-menu-modal"
				   data-bs-toggle="modal"
				   data-bs-target="#menuEditModal"
				   data-id="{{ $node->id }}"
				   data-status="{{ $node->publish }}"
				   data-title="{{ $node->title }}"
				   data-type="{{ $node->type }}"
				   data-locked="{{ $node->locked_by_admin }}"
				   data-sluglock="{{ $node->slug_lock }}"
				   data-auth="{{ $node->route_has_auth }}"
				   data-slug="{{ $node->slug }}"
				   data-route="{{ $node->route }}"
				   data-entview="{{ $node->entity_view_id }}"
				   data-tagid="{{ $node->tag_id }}"
				   data-objectid="{{ $node->object_id }}"
				   data-url="{{ $node->url }}">
					<i class="las la-edit"></i>
				</a>
			@else
				<div class="action-icon-disabled text-muted">
					<i class="las la-edit"></i>
				</div>
			@endif
		</div>

		<div class="menu-delete col-2 col-sm-1 {{ $rowstyle }}">
			@if ($node->depth > 0)
				@if($node->locked_by_admin == 0 || Auth::user()->isAn('administrator'))
					@if($node->isLeaf() || (!$node->isLeaf() && sizeof($node->children) == 0))
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
		</div>

	</div>

	<ul class="children">

		@if (!$node->isLeaf())
			<ul class="children">
				@if(!empty($node->children))
					@foreach ($node->children as $node)
						@include('lara-admin::menuitem.index.content_render', $node)
					@endforeach
				@endif
			</ul>
		@endif

	</ul>

</li>








