<!-- Modal Create -->
<div class="modal fade" id="menuCreateModal" data-bs-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			{{ html()->form('POST', route('admin.menuitem.store'))
				->attributes(['accept-charset' => 'UTF-8'])
				->class('needs-validation')
				->novalidate()
				->open() }}

			<div class="modal-header">
				<h5 class="modal-title">{{ _lanq('lara-admin::menuitem.boxtitle.new_menu_item') }}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'new_title') }}
					</x-slot>
					{{ html()->text('new_title', old('new_title'))->class('form-control')->required() }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.parent').':', 'new_parent_id') }}
					</x-slot>

					<select class="form-select form-select-sm"
					        id="new_parent_id"
					        name="new_parent_id"
					        data-control="select2"
					        data-hide-search="true">
						@foreach($data->parents as $menuParent)
							<option value="{{ $menuParent['id'] }}" @if($menuParent['disabled']) disabled @endif >
								{{ $menuParent['title'] }}
							</option>
						@endforeach
					</select>

				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.type').':', 'new_type') }}
					</x-slot>
					{{ html()->select('new_type', $data->menutypes, old('new_type', 'page'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true')->attributes(['onchange' => 'toggleMenuCreateOptions(this.value)']) }}
				</x-formrow>

				<div id="row_menu_page_create">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.page').':', 'new_object_id') }}
						</x-slot>
						{{ html()->select('new_object_id', $data->pages, old('new_object_id', 'new'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<div id="row_menu_view_create" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.view').':', 'new_entity_view_id') }}
						</x-slot>
						{{ html()->select('new_entity_view_id', [null=>'- Please select view -'] + $data->entviews, old('new_entity_view_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<div id="row_menu_form_view_create" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.view').':', 'new_entity_form_view_id') }}
						</x-slot>
						{{ html()->select('new_entity_form_view_id', $data->entformviews, old('new_entity_form_view_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
				        data-bs-dismiss="modal">{{ _lanq('lara-admin::menuitem.button.close') }}</button>
				{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-danger save-button') }}
			</div>

			{{ html()->hidden('menu_id', $data->menu_id) }}

			{{ html()->hidden('language', $clanguage) }}

			{{ html()->form()->close() }}

		</div>
	</div>
</div>