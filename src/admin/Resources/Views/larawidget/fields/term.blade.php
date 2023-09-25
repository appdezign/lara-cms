<?php
// tags

use Lara\Common\Models\Entity;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

$entityTags = array();
$entityTags['none'] = 'none';

if ($data->object->relentkey) {
	$relentity = Entity::where('entity_key', $data->object->relentkey)->first();
	if ($relentity && $relentity->objectrelations->has_tags) {

		// get tags (default taxonomy)
		$taxonm = Taxonomy::isDefault()->first();
		$tags = Tag::scoped([
			'entity_key'  => $data->object->relentkey,
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

@if($data->object->type == 'module')

	{{-- FILTERTAG --}}
	<x-formrow>
		<x-slot name="label">
			{{ html()->label(_lanq('lara-admin::larawidget.column.term') .':', 'term') }}
		</x-slot>
		{{ html()->select('term', $entityTags, $data->object->term)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
	</x-formrow>

@else

	{{-- Hide disabled fields --}}
	{{ html()->hidden('term', null) }}

@endif