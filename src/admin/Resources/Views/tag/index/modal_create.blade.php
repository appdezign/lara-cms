<!-- Modal Create -->
<div class="modal fade" id="tagCreateModal" data-bs-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">


			{{ html()->form('POST', route('admin.tag.store'))
				->attributes(['accept-charset' => 'UTF-8'])
				->open() }}

			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">{{ ucfirst(_lanq('lara-admin::default.boxtitle.create_new_' . $data->taxonomy->slug)) }}</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::tag.column.title').':', 'title') }}
					</x-slot>
					{{ html()->text('title', old('title'))->class('form-control') }}
				</x-formrow>

				{{ html()->hidden('lead', '') }}
				{{ html()->hidden('body', '') }}
				{{ html()->hidden('publish', 1) }}
				{{ html()->hidden('publish_from', null) }}

				{{ html()->hidden('seo_focus', '') }}
				{{ html()->hidden('seo_title', '') }}
				{{ html()->hidden('seo_description', '') }}
				{{ html()->hidden('seo_keywords', '') }}

				@if($data->taxonomy->has_hierarchy)

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::tag.column.parent').':', 'parent_id') }}
					</x-slot>
					<select class="form-select form-select-sm" name="parent_id" data-control="select2" data-placeholder="Filter" data-hide-search="true">
						@if(!empty($data->tree))
							@foreach($data->tree as $node)
								@include('lara-admin::tag.index.modal_create_render', $node)
							@endforeach
						@endif
					</select>
				</x-formrow>

				@else
					{{ html()->hidden('parent_id', $data->tree[0]->id) }}
				@endif

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ _lanq('lara-admin::menuitem.button.close') }}</button>

				{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-danger save-button') }}

			</div>

			{{ html()->hidden('taxonomy_id', $data->taxonomy->id) }}
			{{ html()->hidden('entity_key', $data->related->getEntityKey()) }}
			{{ html()->hidden('language', $clanguage) }}

			{{ html()->form()->close() }}

		</div>
	</div>
</div>