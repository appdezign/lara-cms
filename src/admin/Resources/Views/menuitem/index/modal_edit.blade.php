<!-- Modal Edit -->
<div class="modal fade" id="menuEditModal" data-bs-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title">{{ _lanq('lara-admin::menuitem.boxtitle.edit_menu_item') }}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.title').':', 'title') }}
					</x-slot>
					{{ html()->text('title', null)->class('form-control') }}
				</x-formrow>

				<div class="row form-group">
					<div class="col-12 col-md-1">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->entity_key.'.column.slug').':', 'slug') }}
					</div>
					<div class="col-12 col-md-1 px-1">

						<div id="js-slug-unlocked" class="d-block">

							<div class="d-inline-block">
								<div class="form-check ps-0">
									{{ html()->checkbox('reset_slug', null, 1)->class('form-check-input ms-0') }}
								</div>
								<span class="fs-9 text-muted">reset</span>
							</div>
							@if(Auth::user()->isAn('administrator'))
								<div class="d-inline-block float-end">
									<div class="form-check ps-0">
										{{ html()->checkbox('slug_lock', null, 1)->class('form-check-input ms-0') }}
									</div>
									<span class="fs-9 text-muted">lock</span>
								</div>
							@endif

						</div>

						<div id="js-slug-locked" class="pt-2 d-none">
							<i class="las la-lock slug-lock-icon float-end"></i>
						</div>

					</div>
					<div class="col-12 col-md-10 col-lg-9">
						{{ html()->text('slug', null)->class('form-control')->disabled() }}
					</div>
				</div>

				<div class="row form-group">
					<div class="col-12 col-md-2 col-lg-2">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->entity_key.'.column.route').':', 'route') }}
					</div>
					<div class="col-2 col-md-2 col-lg-1 text-center menu-slug-edit-prefix">
						<div class="menu-slug-edit-prefix-inner">
							/{{ $clanguage }}/
						</div>
					</div>
					<div class="col-10 col-md-7 col-lg-8 menu-slug-edit-prefix-input">
						{{ html()->text('route', null)->class('form-control')->disabled() }}
					</div>
				</div>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.status').':', 'publish') }}
					</x-slot>
					{{ html()->select('publish', [1 => _lanq('lara-admin::default.value.status_published'), 0 => _lanq('lara-admin::default.value.status_draft')], null)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.type').':', 'type') }}
					</x-slot>
					{{ html()->select('type', $data->menutypes, old('type'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true')->attributes(['onchange' => 'toggleMenuEditOptions(this.value)']) }}
				</x-formrow>

				<div id="row_menu_page_edit" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.page').':', 'object_id') }}
						</x-slot>
						{{ html()->select('object_id', $data->pages, old('object_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<div id="row_menu_view_edit" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label('view:', 'entity_view_id') }}
						</x-slot>
						{{ html()->select('entity_view_id', [null=>'- Please select view -'] + $data->entviews, old('entity_view_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<div id="row_menu_form_view_edit" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.view').':', 'entity_form_view_id') }}
						</x-slot>
						{{ html()->select('entity_form_view_id', $data->entformviews, old('entity_form_view_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<div id="row_menu_tag_edit" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.tag').':', 'tag_id') }}
						</x-slot>
						{{ html()->select('tag_id', $data->tags, old('tag_id'))->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search','true') }}
					</x-formrow>
				</div>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.route_has_auth').':', 'route_has_auth') }}
					</x-slot>
					<div class="form-check">
						{{ html()->checkbox('route_has_auth', null, 1)->class('form-check-input') }}
					</div>
				</x-formrow>

				@if(Auth::user()->isAn('administrator'))
					<x-formrow>
						<x-slot name="label">
							{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.locked_by_admin').':', 'locked_by_admin') }}
						</x-slot>
						<div class="form-check">
							{{ html()->checkbox('locked_by_admin', null, 1)->class('form-check-input') }}
						</div>
					</x-formrow>
				@endif

				{{ html()->hidden('menu_item_id', '') }}

			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
				        data-bs-dismiss="modal">{{ _lanq('lara-admin::default.button.close') }}</button>
				{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-danger save-button') }}
			</div>

		</div>
	</div>
</div>