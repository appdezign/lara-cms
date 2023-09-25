<div class="row">
	<div class="col-12 record-count">
		<div class="float-end">
			<p>
				{{ $data->modelCount }}
				@if($data->modelCount == 1)
					model
				@else
					models
				@endif
				&nbsp;<span class="color-primary">|</span>&nbsp;
				{{ $data->trashCount }}
				in trash

				@if($data->modelCount > 0 && $data->trashCount > 0)
					<a href="{{ route('admin.'.$entity->entity_key.'.edit', ['id' => $data->object->id, 'purge' => 'trash']) }}">
						<i class="las la-trash"></i>
					</a>
				@endif
			</p>
		</div>
	</div>
</div>