<div class="row index-tools-row">

	<div class="col-12 action-tools">

		<a href="{{ route('admin.dashboard.index') }}"
		   class="btn btn-sm btn-icon btn-outline btn-outline-primary float-end">
			<i class="fas fa-reply"></i>
		</a>

	</div>

	<div class="col-1 text-center">
		<div class="select-all-icon">
			<i class="bi bi-arrow-90deg-right"></i>
		</div>
	</div>
	<div class="col-11">
		{{ html()->input('submit', 'cacheclear', _lanq('lara-admin::cache.button.cache_clear'))->id('cacheclear')->class('btn btn-danger swal-cache-clear-confirm') }}
	</div>
</div>