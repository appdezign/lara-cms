<select name="tag" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter"
        data-hide-search="false" onchange="if (this.value) window.location.href=this.value">

	<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['hasrole' => '']) }}">
		@if($data->activeRole)
		{{ _lanq('lara-admin::user.filter.all_roles') }}
		@else
			{{ _lanq('lara-admin::user.filter.select_role') }}
		@endif
	</option>

	@foreach($data->roles as $role)
		{
		<option value="{{ route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['hasrole' => $role]) }}"
		        @if($role == $data->activeRole) selected @endif >
			{{ $role }}
		</option>
	@endforeach

</select>

