<div class="box box-default">
	<x-boxheader cstate="active" collapseid="content">
		{{ _lanq('lara-admin::default.boxtitle.content') }}
	</x-boxheader>
	<div id="kt_card_collapsible_content" class="collapse show">
		<div class="box-body">

			{{-- TITLE --}}
			<x-showrow>
				<x-slot name="label">
			<span class="color-danger">
				{{ _lanq('lara-' . $entity->getModule().'::page.column.title') }}
			</span>
				</x-slot>
				<span class="color-danger">
				{!! $data->object->title !!}
			</span>
			</x-showrow>

			{{-- BODY --}}
			<x-formrow>
				<x-slot name="label">
			<span class="color-danger">
				{{ _lanq('lara-' . $entity->getModule().'::page.column.body') }}
			</span>
				</x-slot>
				<div class="color-danger">
					{!! strip_tags($data->object->body, '<p><br>') !!}
				</div>
			</x-formrow>

		</div>
	</div>
</div>




