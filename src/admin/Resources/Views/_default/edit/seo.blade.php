@if($entity->hasSeo())

	<div class="box box-default">

		<x-boxheader cstate="active" collapseid="seo">
			{{ _lanq('lara-admin::default.boxtitle.seo') }}
		</x-boxheader>

		<div id="kt_card_collapsible_seo" class="collapse show">
			<div class="box-body">

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.seo_title').':', '_seo_title') }}
					</x-slot>
					@if($data->object->seo)
						{{ html()->text('_seo_title', old('_seo_title', $data->object->seo->seo_title))->class('form-control') }}
					@else
						{{ html()->text('_seo_title', null)->class('form-control') }}
					@endif
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.seo_description').':', '_seo_description') }}
					</x-slot>
					@if($data->object->seo)
						{{ html()->textarea('_seo_description', old('_seo_description', $data->object->seo->seo_description))->class('form-control')->rows(4)->maxlength($settngz->seo_desc_max_len) }}
					@else
						{{ html()->textarea('_seo_description', null)->class('form-control')->rows(4)->maxlength($settngz->seo_desc_max_len) }}
					@endif
					<div id="seo_description_counter" class="character-counter"></div>
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.seo_keywords').':', '_seo_keywords') }}
					</x-slot>
					@if($data->object->seo)
						{{ html()->textarea('_seo_keywords', old('_seo_keywords', $data->object->seo->seo_keywords))->class('form-control')->rows(4)->maxlength($settngz->seo_keyw_max_len) }}
					@else
						{{ html()->textarea('_seo_keywords', null)->class('form-control')->rows(4)->maxlength($settngz->seo_keyw_max_len) }}
					@endif
					<div id="seo_keywords_counter" class="character-counter"></div>

				</x-formrow>

				@if(config('lara.has_seo_advanced'))

					<x-showrow>

						<x-slot name="label">
							<img src="{{ asset('assets/admin/img/google.png') }}" width="64" alt="Google" class="m-4"/>
						</x-slot>

						@if($data->object->seo)
							<div class="google-preview">
								<div class="snippet-preview">
									<div class="snippet-width">
										<div class="preview-title">
											<a id="preview-title" href="#">{{ $data->object->seo->seo_title }}</a>
										</div>
										<div class="preview-url">
											{{ config('app.url') }}/{{ $clanguage }}{{ $data->object->menuroute }}
										</div>
										<div id="preview-content" class="preview-content">
											{{ $data->object->seo->seo_description }}
										</div>
									</div>
								</div>
							</div>
						@endif

					</x-showrow>

				@endif

			</div>
		</div>
	</div>


	@if(config('lara.has_seo_advanced'))

		<div class="box box-default">

			<x-boxheader cstate="@if(empty($data->object->seo_focus)) collapsed @else active @endif" collapseid="seo_advanced">
				{{ _lanq('lara-admin::default.boxtitle.seo_advanced') }}
			</x-boxheader>

			<div id="kt_card_collapsible_seo_advanced" class="collapse @if(!empty($data->object->seo_focus)) show @endif ">
				<div class="box-body">

					<x-formrowbadge
							labelfield="_seo_focus"
							labeltext="{{ _lanq('lara-admin::default.column.seo_focus') }}"
							badgeplacement="top"
							badgetitle="{{ _lanq('lara-' . $entity->getModule().'::seo.infobadge.seo_focus_title') }}"
							badgecontent="{{ _lanq('lara-' . $entity->getModule().'::seo.infobadge.seo_focus_body') }}">

						<a class="btn btn-sm btn-primary float-end" id="openGoogleTrendsModal">get trends</a>

						@if($data->object->seo)
							{{ html()->text('_seo_focus', old('_seo_focus', $data->object->seo->seo_focus))->class('form-control')->id('_seo_focus')->style(['width' => '80%']) }}
						@else
							{{ html()->text('_seo_focus', null)->class('form-control')->id('_seo_focus')->style(['width' => '80%']) }}
						@endif

					</x-formrowbadge>

				</div>
			</div>
		</div>


		@include('lara-admin::_partials.google_trends')

	@else
		{{ html()->hidden('_seo_focus', '') }}
	@endif

@endif

@section('scripts-after')

	@parent

	@if($entity->hasSeo())
		<script>
			$('#_seo_title').keyup(function () {
				var previewTitle = $(this).val() ? $(this).val() : '[ {{ _lanq('lara-admin::default.column.seo_title') }} ]';
				$('#preview-title').text(previewTitle);
			});
			$('#_seo_description').keyup(function () {
				var previewDescription = $(this).val() ? $(this).val() : '[ {{ _lanq('lara-admin::default.column.seo_description') }} ]';
				$('#preview-content').text(previewDescription);
			});
		</script>


		<script>

			$(document).ready(function () {

				var description_max = {{ $settngz->seo_desc_max_len }};

				var descr_start_length = $('#_seo_description').val().length;
				var descr_start_remaining = description_max - descr_start_length;
				$('#seo_description_counter').html(descr_start_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');

				$('#_seo_description').keyup(function () {
					var description_length = $('#_seo_description').val().length;
					var description_remaining = description_max - description_length;
					$('#seo_description_counter').html(description_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');
				});
			});

		</script>

		<script>

			$(document).ready(function () {

				var keywords_max = {{ $settngz->seo_keyw_max_len }};

				var keyw_start_length = $('#_seo_keywords').val().length;
				var keyw_start_remaining = keywords_max - keyw_start_length;
				$('#seo_keywords_counter').html(keyw_start_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');

				$('#_seo_keywords').keyup(function () {
					var keywords_length = $('#_seo_keywords').val().length;
					var keywords_remaining = keywords_max - keywords_length;
					$('#seo_keywords_counter').html(keywords_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');
				});
			});

		</script>

		<script type="text/javascript" src="https://ssl.gstatic.com/trends_nrtr/1435_RC10/embed_loader.js"></script>

		<script>

			$('#openGoogleTrendsModal').on('click', function () {

				// get focus word from input on main page
				var focusKeyword = $('#_seo_focus').val();

				if (!focusKeyword) {
					alert('vul eerst een trefwoord in');
				} else if (/\s/.test(focusKeyword)) {
					alert('kies 1 trefwoord');
				} else {

					$('#googleTrendsModal').modal('show');

					$('#_seo_focus').val(focusKeyword);

					$('#googleTrendsTime').empty();
					$('#googleTrendsGeo').empty();
					$('#googleTrendsGeo1').empty();
					$('#googleTrendsGeo2').empty();
					$('#googleTrendsRel').empty();
					$('#googleTrendsRel1').empty();
					$('#googleTrendsRel2').empty();

					getTrends('googleTrendsTime', "TIMESERIES", focusKeyword, null);
					getTrends('googleTrendsGeo', "GEO_MAP", focusKeyword, null);
					getTrends('googleTrendsRel', "RELATED_QUERIES", focusKeyword, null);

				}


			});

			$('#getGoogleTrends').on('click', function () {

				// get focus word from input on the modal
				var focusKeyword = $('#_seo_focus').val();
				var secondaryKeyword = $('#_secondary').val();

				if (!focusKeyword) {
					alert('vul eerst een trefwoord in');
				} else if (/\s/.test(focusKeyword)) {
					alert('kies een trefwoord zonder spaties');
				} else if (/\s/.test(secondaryKeyword)) {
					alert('kies een trefwoord zonder spaties');
				} else {

					$('#googleTrendsTime').empty();
					$('#googleTrendsGeo').empty();
					$('#googleTrendsGeo1').empty();
					$('#googleTrendsGeo2').empty();
					$('#googleTrendsRel').empty();
					$('#googleTrendsRel1').empty();
					$('#googleTrendsRel2').empty();

					getTrends('googleTrendsTime', "TIMESERIES", focusKeyword, secondaryKeyword);
					getTrends('googleTrendsGeo', "GEO_MAP", focusKeyword, secondaryKeyword);
					getTrends('googleTrendsRel', "RELATED_QUERIES", focusKeyword, secondaryKeyword);
				}

			});

			function getTrends(divId, trendsType, focusKeyword, secondaryKeyword) {

				var myMainDiv = document.getElementById(divId);
				var myPrimaryDiv = document.getElementById(divId + '1');
				var mySecondaryDiv = document.getElementById(divId + '2');

				if (secondaryKeyword) {

					if (trendsType == 'TIMESERIES') {

						trends.embed.renderExploreWidgetTo(myMainDiv, trendsType, {
							"comparisonItem": [
								{
									"keyword": focusKeyword,
									"geo": "NL",
									"time": "today 12-m"
								},
								{
									"keyword": secondaryKeyword,
									"geo": "NL",
									"time": "today 12-m"
								}
							], "category": 0, "property": ""
						}, {
							"exploreQuery": "q=" + focusKeyword + "," + secondaryKeyword + "&geo=NL&date=today 12-m",
							"guestPath": "https://trends.google.com:443/trends/embed/"
						});

					} else {

						trends.embed.renderExploreWidgetTo(myPrimaryDiv, trendsType + '_0', {
							"comparisonItem": [
								{
									"keyword": focusKeyword,
									"geo": "NL",
									"time": "today 12-m"
								},
								{
									"keyword": secondaryKeyword,
									"geo": "NL",
									"time": "today 12-m"
								}
							], "category": 0, "property": ""
						}, {
							"exploreQuery": "q=" + focusKeyword + "," + secondaryKeyword + "&geo=NL&date=today 12-m",
							"guestPath": "https://trends.google.com:443/trends/embed/"
						});

						trends.embed.renderExploreWidgetTo(mySecondaryDiv, trendsType + '_1', {
							"comparisonItem": [
								{
									"keyword": focusKeyword,
									"geo": "NL",
									"time": "today 12-m"
								},
								{
									"keyword": secondaryKeyword,
									"geo": "NL",
									"time": "today 12-m"
								}
							], "category": 0, "property": ""
						}, {
							"exploreQuery": "q=" + focusKeyword + "," + secondaryKeyword + "&geo=NL&date=today 12-m",
							"guestPath": "https://trends.google.com:443/trends/embed/"
						});
					}


				} else {

					trends.embed.renderExploreWidgetTo(myMainDiv, trendsType, {
						"comparisonItem": [{
							"keyword": focusKeyword,
							"geo": "NL",
							"time": "today 12-m"
						}], "category": 0, "property": ""
					}, {
						"exploreQuery": "q=" + focusKeyword + "&geo=NL&date=today 12-m",
						"guestPath": "https://trends.google.com:443/trends/embed/"
					});
				}


			}
		</script>

	@endif

@endsection




