<x-showrow>
	<x-slot name="label">
		{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title') }}
	</x-slot>
	{!! $data->object->title !!}
</x-showrow>

<div class="box box-default ">

	<x-boxheader cstate="active" collapseid="tagitems">
		{{ _lanq('lara-eve::' . $data->related->getEntityKey() . '.entity.entity_plural') }}
	</x-boxheader>

	<div id="kt_card_collapsible_tagitems" class="collapse show">
		<div class="box-body">

			@foreach($data->relobjects as $relobj)

					<?php $tagval = in_array($relobj->id, $data->tagobjects) ? 1 : 0; ?>

				<div class="row mb-5">
					<div class="col-1">
						<div class="form-check">
							{{ html()->checkbox('_related_object_' . $relobj->id, $tagval, 1)->class('form-check-input js-check')->disabled() }}
						</div>
					</div>
					<div class="col-11">
						{{ $relobj->title }}
					</div>
				</div>

			@endforeach
		</div>
	</div>

</div>




