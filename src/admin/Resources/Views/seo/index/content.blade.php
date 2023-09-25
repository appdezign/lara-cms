<div class="box box-menu">
	<div class="box-header box-header-menu">
		<div class="row">
			<div class="col-8 col-sm-6">
				menu
			</div>

			<div class="d-none d-sm-block col-1 text-center">
				Title
			</div>
			<div class="d-none d-sm-block col-1 text-center">
				Descr
			</div>
			<div class="d-none d-sm-block col-1 text-center">
				Key
			</div>
			<div class="d-none d-sm-block col-1">
				&nbsp;
			</div>

			<div class="col-2 col-sm-1 text-center">
				<div class="d-none d-sm-block">
					{{ _lanq('lara-admin::default.button.view') }}
				</div>
			</div>
			<div class="col-2 col-sm-1 text-center">
				<div class="d-none d-sm-block">
					{{ _lanq('lara-admin::default.button.edit') }}
				</div>
			</div>

		</div>

	</div>
	<!-- /.box-header -->
	<div class="box-body p-0">

		<ul class="nested-set">
			@if(!empty($data->tree))
				@foreach($data->tree as $node)
					@include('lara-admin::seo.index.content_render', [$node, $entity])
				@endforeach
			@endif
		</ul>

	</div>

</div>






