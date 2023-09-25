@if($entity->hasUser() && $entity->getShowAuthor())
	<div class="box box-default">

		<x-boxheader cstate="active" collapseid="author">
			{{ _lanq('lara-admin::default.boxtitle.author') }}
		</x-boxheader>

		<div id="kt_card_collapsible_author" class="collapse show">
			<div class="box-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.author').':', 'user_id') }}
					</x-slot>
					{{ html()->select('user_id', $data->authors, null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true') }}
				</x-formrow>

			</div>
		</div>
	</div>

@endif

