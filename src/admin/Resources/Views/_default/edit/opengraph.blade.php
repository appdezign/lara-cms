<?php
$og = $data->object->opengraph;
$og_descr_max = (isset($settngz->og_descr_max)) ? $settngz->og_descr_max : 300;
?>

@if($entity->hasOpengraph())

	<div class="box box-default">

		<x-boxheader cstate="active" collapseid="opengraph">
			{{ _lanq('lara-admin::default.boxtitle.opengraph') }}
		</x-boxheader>

		<div id="kt_card_collapsible_opengraph" class="collapse show">
			<div class="box-body">

				<div class="row form-group">
					<div class="col-12 col-md-2">
						<p>Preview</p>
					</div>
					<div class="col-12 col-md-10 col-lg-9 text-center">

						<div class="og-preview">

							@if($og && $og->og_image)
								<img src="{{ route('imgcache', ['width' => 1200, 'height' => 630, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $og->og_image]) }}">
							@elseif($data->object->media->count())
								<img src="{{ route('imgcache', ['width' => 1200, 'height' => 630, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $data->object->featured->filename]) }}">
							@else
								<img src="https://via.placeholder.com/1200x630/e8ecf0/d4d8dc">
							@endif

							<div class="og-preview-content">

								<div class="og-preview-sitename">
									@if(isset($settngz->og_site_name))
										{{ strtoupper($settngz->og_site_name) }}
									@else
										(og:site_name not defined)
									@endif
								</div>

								<h3 class="og-preview-title">
									@if($og && $og->og_title)
										{{ $og->og_title }}
									@else
										{{ $data->object->title }}
									@endif
								</h3>

								<div class="og-preview-text">
									@if($og && $og->og_description)
										{{ $og->og_description }}
									@elseif($data->object->lead)
										{{ str_limit(strip_tags($data->object->lead), $og_descr_max, '') }}
									@elseif($data->object->body)
										{{ str_limit(strip_tags($data->object->body), $og_descr_max, '') }}
									@else
										(no description available)
									@endif
								</div>

							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>


	<div class="box box-default">


		<x-boxheader cstate="@if(is_null($og) || !is_null($og) && $og->og_title == '' && $og->og_description == '') collapsed @else active @endif " collapseid="og_advanced">
			{{ _lanq('lara-admin::default.boxtitle.og_advanced') }}
		</x-boxheader>

		<div id="kt_card_collapsible_og_advanced" class="collapse @if(!empty($og) && ($og->og_title != '' || $og->og_description != '')) show @endif ">
			<div class="box-body">
				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.og_title').':', '_og_title') }}
					</x-slot>
					@if($og)
						{{ html()->text('_og_title', old('_og_title', $og->og_title))->class('form-control') }}
					@else
						{{ html()->text('_og_title', null)->class('form-control') }}
					@endif
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.og_description').':', '_og_description') }}
					</x-slot>
					@if($og)
						{{ html()->textarea('_og_description', old('_og_description', $og->og_description))->class('form-control')->rows(4)->maxlength($og_descr_max) }}
					@else
						{{ html()->textarea('_og_description', null)->class('form-control')->rows(4)->maxlength($og_descr_max) }}
					@endif
					<div id="og_description_counter" class="character-counter"></div>
				</x-formrow>

				<x-formrow>
					<x-slot name="label">
						{{ html()->label(_lanq('lara-admin::default.column.og_image').':', '_og_image') }}
					</x-slot>
					<select id="ogImagePicker" name="_og_image">
						@foreach($data->object->media as $image)
							<option value="{{ $image->filename }}"
							        @if($og && $image->filename == $og->og_image) selected
							        @endif data-img-src='{{ route('imgcache', ['width' => 100, 'height' => 100, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $image->filename]) }}'>{{ $image->filename }}</option>
						@endforeach
						<option value="" @if($og && $og->og_image == '') selected
						        @endif data-img-src="https://via.placeholder.com/100x100/e8ecf0/999999?text=default">
							default
						</option>
					</select>

				</x-formrow>

			</div>
		</div>
	</div>
@endif


@section('scripts-after')

	@parent

	@if($entity->hasOpengraph())

		<script>

			$(document).ready(function () {

				var og_descr_max = {{ $og_descr_max }};

				var og_descr_start_length = $('#_og_description').val().length;
				var og_descr_start_remaining = og_descr_max - og_descr_start_length;
				$('#og_description_counter').html(og_descr_start_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');

				$('#_og_description').keyup(function () {
					var og_description_length = $('#_og_description').val().length;
					var og_description_remaining = og_descr_max - og_description_length;
					$('#og_description_counter').html(og_description_remaining + ' {{ _lanq("lara-admin::seo.message.characters_remaining") }}');
				});
			});

			$("#ogImagePicker").imagepicker({
				hide_select: true,
			});

		</script>

	@endif

@endsection

