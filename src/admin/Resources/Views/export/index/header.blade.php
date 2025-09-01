<div class="row index-toolbar">
	<div class="col-3">
		<div class="filters">
			&nbsp;
		</div>
	</div>

	<div class="col-5">
		<div class="search">
			&nbsp;
		</div>
	</div>

	<div class="col-4">
		<div class="tools d-flex flex-row-reverse gap-3">
			<a href="{{ route('admin.'.$entity->getEntityRouteKey().'.export', 0) }}"
			   class="btn btn-sm btn-icon btn-outline btn-outline-primary"
			   title="Copy language content">
				<i class="fal fa-file-export"></i>
			</a>
		</div>
	</div>

</div>






