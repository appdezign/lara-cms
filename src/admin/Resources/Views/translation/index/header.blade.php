<div class="row">

	<div class="d-none d-sm-block col-2 tools language-selector">
		<select name="group" class="form-select form-select-sm" data-control="select2" data-hide-search="true"
		        onchange="if (this.value) window.location.href=this.value">

			@foreach($data->clanguages as $clang)
				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['clanguage' => $clang->code]) }}"
				        @if($clang->code == $clanguage) selected @endif >
					{{ strtoupper($clang->code) }}
				</option>
			@endforeach

		</select>
	</div>

	<div class="col-5 col-sm-2 tools">

		<select name="module" class="form-select form-select-sm" data-control="select2" data-hide-search="true"
		        onchange="if (this.value) window.location.href=this.value">

			@foreach($data->modules as $mod)
				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['module' => $mod->key]) }}"
				        @if($data->filters->module == $mod->key) selected @endif >
					{{ substr($mod->key, 5) }}
				</option>

			@endforeach
		</select>

	</div>

	<div class="d-none d-sm-block col-2 tools">

		@if($data->filters->search == false && $data->filters->missing == false)
			<select name="group" class="form-select form-select-sm" data-control="select2"
			        onchange="if (this.value) window.location.href=this.value">
				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['module' => $data->filters->module, 'cgroup' => '', 'tag' => '']) }}">
					{{ _lanq('lara-admin::default.form.please_select') }}
				</option>

				@if($data->groups)
					{
					@foreach($data->groups as $group)
						<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['module' => $data->filters->module, 'cgroup' => $group]) }}"
						        @if($group == $data->filters->filtergroup) selected @endif >
							{{ $group }}
						</option>
					@endforeach
				@endif

			</select>
		@endif

	</div>
	<div class="d-none d-sm-block col-2 tools">

		@if($data->filters->search == false && $data->filters->missing == false)
			<select name="tag" class="form-select form-select-sm" data-control="select2"
			        onchange="if (this.value) window.location.href=this.value">
				<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['module' => $data->filters->module, 'cgroup' => $data->filters->filtergroup, 'tag' => '']) }}">
					Filter
				</option>

				@if($data->tags)
					@foreach($data->tags as $tag)
						<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['module' => $data->filters->module, 'cgroup' => $data->filters->filtergroup, 'tag' => $tag]) }}"
						        @if($tag == $data->filters->filtertaxonomy) selected @endif >
							{{  $tag }}
						</option>
					@endforeach
				@endif
			</select>
		@else
			@if($data->filters->missing == false)
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index') }}"
				   class="btn btn-sm btn-outline btn-outline-primary float-end">
					{{ _lanq('lara-admin::default.form.reset_search') }}
				</a>
			@endif
		@endif

	</div>
	<div class="col-7 col-sm-4 tools">
		<div class="tools d-flex flex-row-reverse gap-3">

			<a class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   data-bs-toggle="modal" data-bs-backdrop="static" data-bs-target="#translationCreateModal">
				<i class="fal fa-plus"></i>
			</a>

			@if($data->needsync == true)
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export') }}"
				   class="js-export-button btn btn-sm btn-icon btn-danger"
				   title="Export from DB to File">
					<i class="far fa-file-export"></i>
				</a>
			@else
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export') }}"
				   class="js-export-button btn btn-sm btn-icon btn-outline btn-outline-primary"
				   title="Export from DB to File">
					<i class="far fa-file-export"></i>
				</a>
			@endif

			<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.import') }}"
			   class="js-import-button btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="Import from File to DB">
				<i class="far fa-file-import"></i>
			</a>

			@if($data->filters->missing == true)
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['missing' => 'false']) }}"
				   class="btn btn-sm btn-icon btn-success" title="show all">
					<i class="fas fa-exclamation-circle"></i>
				</a>
			@else
				<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.index', ['missing' => 'true']) }}"
				   class="btn btn-sm btn-icon btn-outline btn-outline-primary" title="show missing translations">
					<i class="fas fa-exclamation-circle"></i>
				</a>
			@endif

			<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.check') }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary" title="check all translations">
				<i class="fas fa-first-aid"></i>
			</a>

		</div>
	</div>

</div>

@if($data->needsync == true)
	<div class="row text-center ">
		<div class="col">
			<div class="mt-6 p-2 bg-orange-light">
				The Database contains new translations. Please sync your translations to file.
			</div>
		</div>
	</div>
@endif

