@foreach($data->tags as $taxonomy => $tags)

	<div class="box box-default @if($tags[0]->isLeaf()) d-none @endif">

		<x-boxheader cstate="active" collapseid="taxonomy_{{$loop->index}}">
			{{ _lanq('lara-admin::default.taxonomy.' . $taxonomy) }}
		</x-boxheader>

		<div id="kt_card_collapsible_taxonomy_{{ $loop->index }}" class="collapse show">
			<div class="box-body">

				<ul class="nested-set">
					@if(!empty($tags))
						@foreach($tags as $node)
							@include('lara-admin::_partials.tags_render', $node)
						@endforeach
					@endif
				</ul>

			</div>
		</div>
	</div>

@endforeach