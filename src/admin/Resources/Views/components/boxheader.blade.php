{{-- $cstate values: active/collapsed --}}
<div class="box-header with-border collapsible cursor-pointer rotate {{ $cstate }}"
     data-bs-toggle="collapse"
     data-bs-target="#kt_card_collapsible_{{ $collapseid }}">
	<h3 class="box-title">{{ $slot }}</h3>
	<div class="card-toolbar rotate-180">
		<span class="svg-icon svg-icon-1">
			@include('lara-admin::_icons.arrowdown_svg')
		</span>
	</div>
</div>

