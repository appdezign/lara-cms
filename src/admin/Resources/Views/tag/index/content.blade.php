<div>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
		@foreach($data->taxonomies as $taxonm)
			@if($taxonm->is_default || config('lara-admin.taxonomy.show_default_only') == false)
			<li role="presentation" class="nav-item">
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['entity' => $data->related->getEntityKey(), 'taxonomy' => $taxonm->slug]) }}" class="nav-link @if($taxonm->slug == $data->taxonomy->slug) active @endif">
					{{ ucfirst(_lanq('lara-admin::default.taxonomy.' . Str::plural($taxonm->slug))) }}
				</a>
			</li>
			@endif
		@endforeach
	</ul>

	<div class="box box-default">
		<div class="box-header with-border">
			<h3 class="box-title">
				{{ ucfirst(_lanq('lara-admin::default.taxonomy.' . Str::plural($data->taxonomy->slug))) }}
			</h3>

		</div>
		<!-- /.box-header -->
		<div class="box-body">

			<ul class="nested-set">
				@if(!empty($data->tree))
					@foreach($data->tree as $node)
						@include('lara-admin::tag.index.content_render', $node)
					@endforeach
				@endif
			</ul>

		</div>

	</div>

</div>



