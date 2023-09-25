<?php

use Lara\Common\Models\Entity;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

$relatableEntities = Entity::where('entity_key', '!=', 'page')
	->whereHas('objectrelations', function ($query) {
		$query->where('is_relatable', 1);
	})->pluck('entity_key');

$relents = array();
foreach ($relatableEntities as $relatableEntity) {
	$relents[$relatableEntity] = $relatableEntity;
}

$widgetEntity = Entity::where('entity_key', 'larawidget')->first();
$hookcolumn = $widgetEntity->customcolumns->where('fieldname', 'hook')->first();
$hooks = array_map('trim', explode(',', $hookcolumn->fielddata));
$hooks = array_combine($hooks, $hooks);

?>

<table class="table table-lara table-row-bordered">
	<thead>
		<tr>
			<th class="w-80">&nbsp;</th>
			<th class="w-10">&nbsp;</th>
			<th class="w-10">&nbsp;</th>
		</tr>
	</thead>
	<tbody>

		@foreach($data->object->widgets as $larawidget)

				<?php
				// tags

				$entityTags = array();
				$entityTags['none'] = 'none';

				if ($larawidget->relentkey) {

					$relentity = Entity::where('entity_key', $larawidget->relentkey)->first();
					if ($relentity && $relentity->objectrelations->has_tags) {
						// get tags

						// get tags (default taxonomy)
						$taxonm = Taxonomy::isDefault()->first();
						$tags = Tag::scoped([
							'entity_key'  => $larawidget->relentkey,
							'language'    => $clanguage,
							'taxonomy_id' => $taxonm->id
						])
							->defaultOrder()
							->get()
							->toArray();

						foreach ($tags as $tag) {
							if ($tag['parent_id']) {
								$slug = $tag['slug'];
								$title = $tag['title'];
								$entityTags[$slug] = $title;
							}
						}
					}
				}
				?>

			<tr>
				<td>

					<div id="larawidget_info_{{ $larawidget->id }}" class="row larawidget-info-panel" >

						<div class="col-3">{{ $larawidget->title }}</div>
						<div class="col-2">{{ strtoupper($larawidget->type) }}</div>
						<div class="col-2">{{ $larawidget->hook }}</div>
						<div class="col-1">{{ $larawidget->sortorder }}</div>
						<div class="col-1">{{ $larawidget->relentkey }}</div>
						<div class="col-2">{{ $larawidget->filtertaxonomy }}</div>
						<div class="col-1">
							@if($larawidget->image_required)
								<i class="fal fa-file-image color-black"></i>
							@else
								<i class="fal fa-file-image text-muted"></i>
							@endif
						</div>
					</div>

					<div id="larawidget_edit_{{ $larawidget->id }}" class="larawidget-edit-panel" style="display:none;">

						<div class="row mb-6">
							<div class="col-12 text-end"><h4>Quick Edit</h4></div>
						</div>

						{{-- TITLE --}}
						<div class="row form-group">
							<div class="col-3">
								{{ html()->label('title:', '_larawidget_title_' . $larawidget->id) }}
							</div>
							<div class="col-9">
								{{ html()->text('_larawidget_title_' . $larawidget->id, $larawidget->title)->class('form-control') }}
							</div>
						</div>

						{{-- HOOK --}}
						<div class="row form-group">
							<div class="col-3">
								{{ html()->label('hook:', '_larawidget_hook_' . $larawidget->id) }}
							</div>
							<div class="col-9">
								{{ html()->select('_larawidget_hook_' . $larawidget->id, $hooks, $larawidget->hook)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
							</div>
						</div>

						@if($larawidget->type == 'text')

							{{-- BODY --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('body:', '_larawidget_body_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									{{ html()->textarea('_larawidget_body_' . $larawidget->id, $larawidget->body)->class('form-control tiny')->rows(8) }}
								</div>
							</div>

						@elseif($larawidget->type == 'module')

							{{-- ENTITY --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('entity:', '_larawidget_relent_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									{{ $larawidget->relentkey }}
								</div>
							</div>

							{{-- FILTERTAG --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('tag:', '_larawidget_filtertaxonomy_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									{{ html()->select('_larawidget_filtertaxonomy_' . $larawidget->id, $entityTags, $larawidget->term)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
								</div>
							</div>


							{{-- IMAGE REQUIRED --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('image required:', '_larawidget_imgreq_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									<div class="form-check m-0">
										{{ html()->hidden('_larawidget_imgreq_' . $larawidget->id, 0) }}
										{{ html()->checkbox('_larawidget_imgreq_' . $larawidget->id, $larawidget->imgreq, 1)->class('form-check-input') }}
									</div>
								</div>
							</div>

							{{-- MAX ITEMS --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('max items:', '_larawidget_maxitems_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									{{ html()->input('number', '_larawidget_maxitems_' . $larawidget->id, $larawidget->maxitems)->class('form-control')->attributes(['min' => '0', 'step' => '1']) }}
								</div>
							</div>

							{{-- USE CACHE --}}
							<div class="row form-group">
								<div class="col-3">
									{{ html()->label('use cache:', '_larawidget_usecache_' . $larawidget->id) }}
								</div>
								<div class="col-9">
									<div class="form-check m-0">
										{{ html()->hidden('_larawidget_usecache_' . $larawidget->id, 0) }}
										{{ html()->checkbox('_larawidget_usecache_' . $larawidget->id, $larawidget->usecache, 1)->class('form-check-input') }}
									</div>
								</div>
							</div>

						@endif

						{{-- TEMPLATE --}}
						<div class="row form-group">
							<div class="col-3">
								{{ html()->label('custom template:', '_larawidget_template_' . $larawidget->id) }}
							</div>
							<div class="col-9">
								{{ html()->text('_larawidget_template_' . $larawidget->id, $larawidget->template)->class('form-control') }}
							</div>
						</div>

						{{-- POSITION --}}
						<div class="row form-group">
							<div class="col-3">
								{{ html()->label('sort order:', '_larawidget_sortorder_' . $larawidget->id) }}
							</div>
							<div class="col-9">
								{{ html()->input('number', '_larawidget_sortorder_' . $larawidget->id, $larawidget->sortorder)->class('form-control')->attributes(['min' => '0', 'step' => '1']) }}
							</div>
						</div>

						{{-- SAVE --}}
						<div class="row form-group">
							<div class="col-12 mt-5">
								{{ html()->button(_lanq('lara-admin::default.button.larawidgetsave'),'submit', '_save_larawidget')->value('_save_larawidget_'.$larawidget->id)->class('btn btn-sm btn-danger float-end') }}
							</div>
						</div>

					</div>

				</td>
				<td class="text-center action-icons">
					<a href="javascript:void(0)" onclick="larawidgetDataToggler({{ $larawidget->id }});">
						<i class="fal fa-bolt"></i>
					</a>
				</td>
				<td class="text-center action-icons">
					<a href="{{ route('admin.larawidget.edit', ['id' => $larawidget->id, 'returnpage' => $data->object->id, 'returnwidget' => $larawidget->id]) }}">
						<i class="las la-edit"></i>
					</a>
				</td>
			</tr>
		@endforeach
	</tbody>

</table>