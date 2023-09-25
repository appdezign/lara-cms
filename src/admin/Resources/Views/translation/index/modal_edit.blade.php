<!-- Modal Edit -->
<div class="modal fade" id="translationEditModal" data-bs-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"
				    id="myModalLabel">{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.entity.edit_translation') }}
					- {{ strtoupper($clanguage) }}</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.cgroup').':', 'cgroup') }}
					</x-slot>
					{{ html()->text('cgroup', null)->class('form-control')->disabled() }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.tag').':', 'tag') }}
					</x-slot>
					{{ html()->text('tag', null)->class('form-control')->disabled() }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.key').':', 'key') }}
					</x-slot>
					{{ html()->text('key', null)->class('form-control')->disabled() }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.value').':', 'value') }}
					</x-slot>
					{{ html()->textarea('value', null)->class('form-control')->rows(4) }}
				</x-formrow>

				{{ html()->hidden('translation_id', '') }}

				<x-formrow>
					<x-slot name="label">
						{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.general.all_translations') }}
					</x-slot>
					<div id="alltrans"></div>
				</x-formrow>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
				        data-bs-dismiss="modal">{{ _lanq('lara-admin::menuitem.button.close') }}</button>

				{{ html()->button(_lanq('lara-admin::default.button.save'),'submit')->class('btn btn-danger save-button') }}

			</div>
		</div>
	</div>
</div>