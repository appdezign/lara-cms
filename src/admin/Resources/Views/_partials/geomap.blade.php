@if(is_numeric($data->object->latitude) && is_numeric($data->object->longitude))
	@if($data->object->latitude != 0 && $data->object->longitude != 0)

		<div class="box box-default">

			<x-boxheader cstate="active" collapseid="geomap">
				{{ _lanq('lara-admin::default.boxtitle.map') }}
			</x-boxheader>

			<div id="kt_card_collapsible_geomap" class="collapse show">
				<div class="box-body">
					<div class="row form-group">
						<div class="col">
							<div class="maps-container no-map-scroll">
								<div class="click-map"></div>
								<div id="map-canvas" class="geo-map-canvas"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>


	@endif
@endif


@section('scripts-after')

	@parent

	@if(is_numeric($data->object->latitude) && is_numeric($data->object->longitude))
		@if($data->object->latitude != 0 && $data->object->longitude != 0)
			<script>

				function initMap() {
					var myLatLng = {lat: {{ $data->object->latitude }}, lng: {{ $data->object->longitude }}};

					var map = new google.maps.Map(document.getElementById('map-canvas'), {
						zoom: 13,
						center: myLatLng
					});

					var marker = new google.maps.Marker({
						position: myLatLng,
						map: map,
						title: '{{ $data->object->title }}'
					});
				}

				$(document).ready(function () {
					$(".no-map-scroll").click(function () {
						$(".click-map").removeClass("click-map");
					});

				});

			</script>
			<script async defer
			        src="https://maps.googleapis.com/maps/api/js?key={{ config('lara.google_maps_api_key') }}&callback=initMap">
			</script>
		@endif
	@endif

@endsection