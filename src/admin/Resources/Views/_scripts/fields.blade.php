<script type="text/javascript">

	document.addEventListener("DOMContentLoaded", function () {

		// datetimepickers for fields
		@foreach ($entity->getCustomColumns() as $cvar)

		@if ($cvar->fieldtype == 'datetime')
		flatpickr("#dtp-{{  $cvar->fieldname }}", {
			dateFormat: "Y-m-d H:i",
			enableTime: true,
			time_24hr: true,
			wrap: true,
		});
		@endif

		@if ($cvar->fieldtype == 'date')
		flatpickr("#dtp-{{  $cvar->fieldname }}", {
			dateFormat: "Y-m-d",
			enableTime: false,
			wrap: true,
		});
		@endif

		@if ($cvar->fieldtype == 'time')
		flatpickr("#dtp-{{  $cvar->fieldname }}", {
			dateFormat: 'H:i',
			noCalendar: true,
			enableTime: true,
			time_24hr: true,
			wrap: true,
		});
		@endif

		@endforeach


	});

</script>
