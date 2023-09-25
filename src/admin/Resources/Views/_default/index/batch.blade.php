<div class="col-1 text-center batch">
	<div class="select-all-icon">
		<i class="bi bi-arrow-90deg-right"></i>
	</div>
</div>
<div class="col-11 batch">

	{{ html()->input('submit', 'batchdelete', _lanq('lara-admin::default.button.batch_delete'))->class('btn btn-sm btn-danger') }}

	{{ html()->input('submit', 'batchpublish', _lanq('lara-admin::default.button.batch_publish'))->class('btn btn-sm btn-info')->style(['margin-left' => '20px']) }}

	{{ html()->input('submit', 'batchunpublish', _lanq('lara-admin::default.button.batch_unpublish'))->class('btn btn-sm btn-info')->style(['margin-left' => '20px']) }}

</div>