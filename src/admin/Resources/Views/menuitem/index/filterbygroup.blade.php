<select name="menugroup_id" class="form-select form-select-sm" data-control="select2" data-placeholder="Filter" data-hide-search="true" onchange="if (this.value) window.location.href=this.value">
	@foreach($data->menus as $menuid => $menuname)
		<option value="{{ route('admin.menuitem.index', ['menu=' . $menuid]) }}"
		        @if($menuid == $data->menu_id) selected @endif>{{ $menuname }}
		</option>
	@endforeach
</select>
