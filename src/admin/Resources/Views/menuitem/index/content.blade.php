<div class="box box-menu">
	<div class="box-header box-header-menu">
		<div class="row">
			<div class="col-12 col-sm-5">
				menu
			</div>
			<div class="d-none d-sm-block col-1">
				&nbsp;
			</div>
			<div class="d-none d-sm-block col-1">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.type') }}
			</div>
			<div class="d-none d-sm-block col-3">
				{{ _lanq('lara-' . $entity->getModule().'::'.$entity->getEntityKey().'.column.content') }}
			</div>
			<div class="d-none d-sm-block col-1 text-center">
				{{ _lanq('lara-admin::default.button.edit') }}
			</div>
			<div class="d-none d-sm-block col-1 text-center">
				{{ _lanq('lara-admin::default.button.delete') }}
			</div>

		</div>

	</div>
	<!-- /.box-header -->
	<div class="box-body p-0">

		<ul class="nested-set">
			@if(!empty($data->tree))
				@foreach($data->tree as $node)
					@include('lara-admin::menuitem.index.content_render', $node)
				@endforeach
			@endif
		</ul>

	</div>

</div>






