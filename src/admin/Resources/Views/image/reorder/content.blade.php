<div class="row">
	<div class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">

		<div class="row">
			<div class="col-4">
				@if($data->object->featured)
					<div class="img-featured-small">
						<div class="img-featured-small-inner"></div>
						<img src="{{ route('imgcache', ['width' => 400, 'height' => 300, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $data->object->featured->filename]) }}" />

						<div class="image-tags">
							<div class="image-tag">featured</div>
						</div>
					</div>
				@endif

			</div>
			<div class="col-4">
				&nbsp;
			</div>
			<div class="col-4">
				@if($data->object->hero)
					<div class="img-featured-small">
						<div class="img-featured-small-inner"></div>
						<img src="{{ route('imgcache', ['width' => 400, 'height' => 300, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $data->object->hero->filename]) }}" />

						<div class="image-tags">
							<div class="image-tag">hero</div>
						</div>
					</div>
				@endif

			</div>
		</div>

		<table class="table table-lara-sortable sortable-images">
			<thead>
				<tr>
					<th class="p-0" style="width:33.33%;">
						&nbsp;
					</th>
					<th class="p-0" style="width:66.67%;">
						&nbsp;
					</th>
				</tr>
			</thead>
			<tbody class="sortable" data-entityname="{{ $entity->getEntityKey() }}">
				@foreach( $data->objects as $image )
					<tr class="sortable-row" data-itemId="{{ $image->id }}">
						<td class="sortable-handle ps-3 pt-6">
							<i class="fal fa-sort fs-4"></i>
						</td>
						<td class="sortable-handle">
							<img src="{{ route('imgcache', ['width' => 160, 'height' => 80, 'fit' => 1, 'fitpos' => 'center', 'quality' => 90, 'filename' => $image->filename]) }}"/>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

	</div>
</div>

