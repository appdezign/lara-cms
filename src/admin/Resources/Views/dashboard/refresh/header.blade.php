<div class="row index-toolbar">

	<div class="col-1 ">
		<div class="select-all-icon">
			<i class="bi bi-arrow-90deg-right"></i>
		</div>
	</div>
	<div class="col-9">

		{{ html()->input('submit', 'refresh', _lanq('lara-admin::ga.button.refresh'))->id('refresh')->class('btn btn-sm btn-danger swal-ga-refresh-confirm') }}

	</div>
	<div class="col-2">
		<a href="{{ route('admin.dashboard.index') }}"
		   class="btn btn-sm btn-icon btn-outline btn-outline-primary float-end">
			<i class="fas fa-reply"></i>
		</a>
	</div>
</div>