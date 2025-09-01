<div class="row index-toolbar">

	<div class="tools d-flex flex-row-reverse gap-3">

		@if($data->force)
			<a href="{{ route('admin.'.$entity->entity_key.'.export', ['force' => 'false']) }}"
			   class="btn btn-sm btn-icon btn-success">
				<i class="las la-unlock"></i>
			</a>
		@else
			<a href="{{ route('admin.'.$entity->entity_key.'.export', ['force' => 'true']) }}"
			   class="btn btn-sm btn-icon btn-danger">
				<i class="las la-lock"></i>
			</a>
		@endif

		<a href="{{ route('admin.'.$entity->entity_key.'.index') }}"
		   class="btn btn-sm btn-icon btn-outline btn-outline-primary">
			<i class="fas fa-reply"></i>
		</a>

	</div>

</div>