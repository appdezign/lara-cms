<!-- Modal Edit -->
<div class="modal fade" id="translationCreateModal" data-bs-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			{{ html()->form('POST', route('admin.translation.store'))
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">New Translation Key - {{ strtoupper($clanguage) }}</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Group:', 'cgroup') }}
					</x-slot>
					{{ html()->select('cgroup', $data->groups + ['new' => ' - Create new group - '], $data->filters->filtergroup)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->attributes(['onchange' => 'toggleTranslationGroup(this.value)']) }}
				</x-formrow>

				<div id="row_translation_group" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label('New Group:', '_new_group') }}
						</x-slot>
						{{ html()->text('_new_group', null)->class('form-control') }}
					</x-formrow>
				</div>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Tag:', 'tag') }}
					</x-slot>
					{{ html()->select('tag', $data->tags + ['new' => ' - Create new tag - '], $data->filters->filtertaxonomy)->class('form-select form-select-sm')->data('control', 'select2')->data('hide-search', 'true')->attributes(['onchange' => 'toggleTranslationTag(this.value)']) }}
				</x-formrow>

				<div id="row_translation_tag" style="display:none">
					<x-formrow>
						<x-slot name="label">
							{{ html()->label('New Tag:', '_new_tag') }}
						</x-slot>
						{{ html()->text('_new_tag', null)->class('form-control') }}
					</x-formrow>
				</div>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Key:', 'key') }}
					</x-slot>
					{{ html()->text('key', null)->class('form-control') }}
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label('Value:', 'value') }}
					</x-slot>
					{{ html()->textarea('value', null)->class('form-control')->rows(4) }}
				</x-formrow>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
				        data-bs-dismiss="modal">{{ _lanq('lara-admin::menuitem.button.close') }}</button>

				{{ html()->button(_lanq('lara-admin::default.button.save'), 'submit')->class('btn btn-danger save-button') }}

			</div>

			{{ html()->hidden('language', $clanguage) }}
			{{ html()->hidden('module', $data->filters->module) }}

			{{ html()->hidden('_filtertaxonomy', $data->filters->filtertaxonomy) }}
			{{ html()->hidden('_filtergroup', $data->filters->filtergroup) }}
			{{ html()->hidden('_missing', $data->filters->missing) }}

			{{ html()->form()->close() }}

		</div>
	</div>
</div>