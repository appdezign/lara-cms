<!--begin::Header-->
<div id="kt_app_header" class="app-header app-header-white">
	<x-closeleft>
		{{ route('admin.'.$data->relatedEntity->getEntityKey().'.edit', ['id' => $data->object->id, 'tab' => 'images']) }}
	</x-closeleft>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-10 offset-md-1">
				&nbsp;
			</div>
		</div>
	</div>

	<x-closeright>
		{{ route('admin.'.$data->relatedEntity->getEntityKey().'.edit', ['id' => $data->object->id, 'tab' => 'images']) }}
	</x-closeright>
</div>
<!--end::Header-->



