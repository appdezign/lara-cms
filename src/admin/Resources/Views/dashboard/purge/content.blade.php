<div class="table-responsive">
	<table class="table table-lara table-row-bordered table-hover">

		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			@foreach($data->purgeable as $entGroup => $ents)

				<tr style="background-color: #eee;">
					<td colspan="3"><h3>{{ $entGroup }}</h3></td>
				</tr>

				@foreach($ents as $ent)
					<tr>
						<td>{{ $ent['entityKey'] }}</td>
						<td>{{ $ent['objectCount'] }}</td>
						<td>
							<ul style="list-style: none;">
								@foreach($ent['objects'] as $obj)
									<li>{{ $obj->id }} - {{ $obj->title }}</li>
								@endforeach
							</ul>
						</td>
					</tr>

				@endforeach
			@endforeach
		</tbody>
	</table>
</div>