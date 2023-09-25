@if($entity->hasLanguage())

	@if($data->langversions->children && sizeof($data->langversions->children) > 0)

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="langversions">
				{{ _lanq('lara-admin::default.boxtitle.language_versions') }}
			</x-boxheader>

			<div id="kt_card_collapsible_langversions" class="collapse show">
				<div class="box-body">

					@if($data->langversions->parent->langcode == $data->object->language)
						<div class="row mb-6">
							<div class="col-12 col-md-2 pt-2">
								{{ _lanq('lara-admin::language.formfield.source') }}:
							</div>
							<div class="col-12 col-md-1">
								<div class="lang-version active">
									{{ $data->langversions->parent->langcode }}
								</div>
							</div>
							<div class="col-12 col-md-9 col-lg-8 pt-2">
								<div class="lang-version-parent-title">{{ $data->langversions->parent->title }}</div>
							</div>
						</div>
					@else
						<div class="row mb-6">
							<div class="col-12 col-md-2 pt-2">
								{{ _lanq('lara-admin::language.formfield.source') }}:
							</div>
							<div class="col-12 col-md-1">
								<div class="lang-version">
									{{ $data->langversions->parent->langcode }}
								</div>
							</div>
							<div class="col-12 col-md-9 col-lg-8 pt-2">

								<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $data->langversions->parent->object_id, 'clanguage' => $data->langversions->parent->langcode]) }}">{{ $data->langversions->parent->title }}</a>
							</div>
						</div>
					@endif

					@foreach($data->langversions->children as $langchild)

						@if($langchild->langcode == $data->object->language)
							<div class="row">
								<div class="col-12 col-md-2 pt-2">
									@if ($loop->first)
										{{ _lanq('lara-admin::language.formfield.versions') }}:
									@endif
								</div>
								<div class="col-12 col-md-1">
									<div class="lang-version active">
										{{ $langchild->langcode }}
									</div>
								</div>
								<div class="col-12 col-md-9 col-lg-8 pt-2">
									<span class="lang-version-child-title">{{ $langchild->title }}</span>
								</div>
							</div>
						@else
							<div class="row">
								<div class="col-12 col-md-2 pt-2">
									@if ($loop->first)
										{{ _lanq('lara-admin::language.formfield.versions') }}:
									@endif
								</div>
								<div class="col-12 col-md-1">
									<div class="lang-version">
										{{ $langchild->langcode }}
									</div>
								</div>
								<div class="col-12 col-md-9 col-lg-8 pt-2">
									<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.edit', ['id' => $langchild->object_id, 'clanguage' => $langchild->langcode]) }}">{{ $langchild->title }}</a>
								</div>
							</div>
						@endif

					@endforeach

				</div>
			</div>

		</div>


	@endif

@endif