<div class="box box-default">
	<x-boxheader cstate="active" collapseid="content">
		{{ _lanq('lara-admin::default.boxtitle.content') }}
	</x-boxheader>
	<div id="kt_card_collapsible_content" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::default.column.seo_title').':', 'seo_title') }}
				</x-slot>
				{{ html()->text('seo_title', $data->object->seo->seo_title)->class('form-control') }}
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::default.column.seo_description').':', 'seo_description') }}
				</x-slot>
				{{ html()->textarea('seo_description', $data->object->seo->seo_description)->class('form-control')->rows(4)->maxlength($settngz->seo_desc_max_len) }}
				<div id="seo_description_counter" class="character-counter"></div>
			</x-formrow>

			<x-formrow>
				<x-slot name="label">
					{{ html()->label(_lanq('lara-admin::default.column.seo_keywords').':', 'seo_keywords') }}
				</x-slot>
				{{ html()->textarea('seo_keywords', $data->object->seo->seo_keywords)->class('form-control')->rows(4)->maxlength($settngz->seo_keyw_max_len) }}
				<div id="seo_keywords_counter" class="character-counter"></div>

			</x-formrow>

		</div>
	</div>
</div>





@section('scripts-after')

	@parent

	<script>

		$(document).ready(function () {

			var description_max = {{ $settngz->seo_desc_max_len }};

			var descr_start_length = $('#seo_description').val().length;
			var descr_start_remaining = description_max - descr_start_length;
			$('#seo_description_counter').html(descr_start_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');

			$('#seo_description').keyup(function () {
				var description_length = $('#seo_description').val().length;
				var description_remaining = description_max - description_length;
				$('#seo_description_counter').html(description_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');
			});
		});

	</script>

	<script>

		$(document).ready(function () {

			var keywords_max = {{ $settngz->seo_keyw_max_len }};

			var keyw_start_length = $('#seo_keywords').val().length;
			var keyw_start_remaining = keywords_max - keyw_start_length;
			$('#seo_keywords_counter').html(keyw_start_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');

			$('#seo_keywords').keyup(function () {
				var keywords_length = $('#seo_keywords').val().length;
				var keywords_remaining = keywords_max - keywords_length;
				$('#seo_keywords_counter').html(keywords_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');
			});
		});

	</script>

@endsection

