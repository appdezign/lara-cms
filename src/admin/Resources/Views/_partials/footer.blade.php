<!--begin::Footer-->
<div id="kt_app_footer" class="app-footer">
	<div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
		<div class="text-muted">
			Copyright &copy; {{ Carbon\Carbon::today()->format('Y') }} <a href="https://www.firmaq.nl" target="_blank">Firmaq
				Media</a>
		</div>
		<div class="d-none d-md-block text-muted">
			@if(config('lara-admin.white_label.active') && !config('lara-admin.white_label.show_lara_versions'))
				Powered by Laravel v{{ app()->version() }} | PHP v{{ phpversion() }}
			@else
				Powered by Lara v{{ $laraversion->version }} | DB v{{ $laradbversion }}  | App v{{ $eveversion }} |
				Laravel v{{ app()->version() }} | PHP v{{ phpversion() }}
			@endif
		</div>
	</div>
</div>
<!--end::Footer-->