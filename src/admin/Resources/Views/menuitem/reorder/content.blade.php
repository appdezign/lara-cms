<div class="box box-default">
	<div class="box-header with-border">
		<h3 class="box-title">{{ $data->menutitle }}</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">

		<div class="row">
			<div class="offset-lg-1 col-lg-10">
				<ol class="sortable">
					@if(!empty($data->tree))
						@foreach($data->tree as $node)
							@include('lara-admin::menuitem.reorder.content_render', $node)
						@endforeach
					@endif
				</ol>
			</div>
		</div>

	</div>

</div>




