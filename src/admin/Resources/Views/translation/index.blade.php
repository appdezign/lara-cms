@extends('lara-admin::layout')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include($data->partials['pagetitle'])
		</div>
	</div>
	<!--end::Toolbar-->


	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			<div class="content-box main-content">
				<div class="content-box-header">
					@include($data->partials['header'])
				</div>
				<div class="content-box-body">

					{{ html()->form('POST', route('admin.'.$entity->getEntityRouteKey().'.batch'))
						->id('translationform')
						->attributes(['accept-charset' => 'UTF-8'])
						->open() }}

					@include($data->partials['content'])
					@include('lara-admin::translation.index.modal_edit')

					{{ html()->hidden('language', $clanguage) }}

					{{ html()->hidden('_module', $data->filters->module) }}
					{{ html()->hidden('_filtertaxonomy', $data->filters->filtertaxonomy) }}
					{{ html()->hidden('_filtergroup', $data->filters->filtergroup) }}
					{{ html()->hidden('_missing', $data->filters->missing) }}

					{{ html()->form()->close() }}

				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->

	@include('lara-admin::translation.index.modal_create')

@endsection

@section('scripts-after')

	<script type="text/javascript">

		const exportButton = document.querySelector('a.js-export-button');
		exportButton.addEventListener('click', e => {
			e.preventDefault();
			const linkUrl = exportButton.getAttribute("href");
			Swal.fire({
				title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
				text: "{{ ucfirst(_lanq('lara-admin::translation.message.overwrite_files')) }}",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
				cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = linkUrl;
				}
			});
		});

		const importButton = document.querySelector('a.js-import-button');
		importButton.addEventListener('click', e => {
			e.preventDefault();
			const linkUrl = importButton.getAttribute("href");
			Swal.fire({
				title: "{{ ucfirst(_lanq('lara-admin::default.message.are_you_sure')) }}",
				text: "{{ ucfirst(_lanq('lara-admin::translation.message.overwrite_database')) }}",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
				cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}"
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = linkUrl;
				}
			});
		});

		$(".open-translation-modal").click(function () {

			var translationID = $(this).data('id');
			var translationGroup = $(this).data('cgroup');
			var translationTag = $(this).data('tag');
			var translationKey = $(this).data('key');
			var translationValue = $(this).data('value');

			var alltranslations = '';
			@foreach($data->clanguages as $clang)
				alltranslations += '{{ $clang->code }}' + ': ';
				alltranslations += $(this).data('value_{{ $clang->code }}');
				alltranslations += '<br><br>';
			@endforeach


			$('input[name="translation_id"]').val(translationID);
			$('input[name="cgroup"]').val(translationGroup);
			$('input[name="tag"]').val(translationTag);
			$('input[name="key"]').val(translationKey);
			$('textarea[name="value"]').val(translationValue);

			$('#alltrans').html(alltranslations);
		});

		function toggleTranslationGroup(val) {
			if (val == 'new') {
				document.getElementById('row_translation_group').style.display = 'block';
			} else {
				document.getElementById('row_translation_group').style.display = 'none';
			}
		}

		function toggleTranslationTag(val) {
			if (val == 'new') {
				document.getElementById('row_translation_tag').style.display = 'block';
			} else {
				document.getElementById('row_translation_tag').style.display = 'none';
			}
		}

	</script>
@endsection