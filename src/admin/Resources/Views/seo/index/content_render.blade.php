<?php $padding = ($node->depth > 0) ? ($node->depth - 1) * 30 : 0; ?>
<?php $nodeclass = $node->isLeaf() ? 'isLeaf' : 'hasChildren'; ?>
<?php $rowstyle = ($node->publish == 1) ? 'published' : 'draft'; ?>

<?php

// dd($node->object);
?>

<li class="{{ $nodeclass }}">

	<div class="row">

		<div class="menu-title col-8 col-sm-6 {{ $rowstyle }}" style="padding-left: {{ $padding }}px">

			@if ($node->depth > 0)
				<div class="child-icon">
					<i class="fal fa-chevron-left"></i>
				</div>
			@endif

			<a href="/{{ $clanguage }}/{{ $node->route }}" target="_blank" class="title">
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
			</a>
		</div>

		<div class="d-none d-sm-block col-1 text-center p-2 {{ $rowstyle }}">
			@if(!empty($node->object))
				@if(!empty($node->object->seo->seo_title))
					<i class="fas fa-check color-primary"></i>
				@endif
			@endif
		</div>
		<div class="d-none d-sm-block col-1 text-center p-2 {{ $rowstyle }}">
			@if(!empty($node->object))
				@if(!empty($node->object->seo->seo_description))
					<i class="fas fa-check color-primary"></i>
				@endif
			@endif
		</div>
		<div class="d-none d-sm-block col-1 text-center p-1 {{ $rowstyle }}">
			@if(!empty($node->object))
				@if(!empty($node->object->seo->seo_keywords))
					<i class="fas fa-check color-primary"></i>
				@endif
			@endif
		</div>
		<div class="d-none d-sm-block col-1 {{ $rowstyle }}">
			&nbsp;
		</div>

		<div class="menu-edit col-2 col-sm-1 {{ $rowstyle }}">
			@if(!empty($node->object))
				<a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#note_details_{{ $node->object->id }}">
					<i class="far fa-eye"></i>
				</a>
			@endif
		</div>

		<div class="menu-edit col-2 col-sm-1 {{ $rowstyle }}">
			@if(!empty($node->object))
				<a href="{{ route('admin.'.$entity->getEntityKey().'.edit', ['id' => $node->object->id]) }}">
					<i class="las la-edit"></i>
				</a>
			@endif
		</div>

	</div>

	@if(!empty($node->object))
		<div id="note_details_{{ $node->object->id }}" class="row my-6 collapse">
			<div class="col-12 col-sm-8 offset-sm-2">
				<div class="card card-border mb-6">
					<div class="card-body">
						<div class="row">
							<div class="col-3 p-2">
								{{ _lanq('lara-admin::default.column.seo_title') }}
							</div>
							<div class="col-9 p-2">
								@if($node->object->seo()->exists())
									{!! $node->object->seo->seo_title !!}
								@endif
							</div>
						</div>
						<div class="row">
							<div class="col-3 p-2">
								{{ _lanq('lara-admin::default.column.seo_description') }}
							</div>
							<div class="col-9 p-2">
								@if($node->object->seo()->exists())
									{!! $node->object->seo->seo_description !!}
								@endif
							</div>
						</div>

						<div class="row">
							<div class="col-3 p-2">
								{{ _lanq('lara-admin::default.column.seo_keywords') }}
							</div>
							<div class="col-9 p-2">
								@if($node->object->seo()->exists())
									{!! $node->object->seo->seo_keywords !!}
								@endif
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	@endif

	<ul class="children">

		@if (!$node->isLeaf())
			<ul class="children">
				@if(!empty($node->children))
					@foreach ($node->children as $node)
						@include('lara-admin::seo.index.content_render', [$node, $entity])
					@endforeach
				@endif
			</ul>
		@endif

	</ul>

</li>








