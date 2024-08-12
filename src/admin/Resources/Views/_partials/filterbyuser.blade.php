<?php $activeUserId = $data->filters->autofilters['user_id'] ?? null; ?>

@if(in_array($entity->getEntityKey(), config('lara-admin.filter_by_user.entities')))

	@if(Auth::user()->mainrole->level >= config('lara-admin.filter_by_user.min_user_level', 100))

		<select name="user_id" class="form-select form-select-sm" data-control="select2" data-placeholder="Select User"
		        data-hide-search="true" onchange="if (this.value) window.location.href=this.value">

			<option value="{!! route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['user_id' => '']) !!}"
			        @if($activeUserId == '')) selected @endif >
				[ {{ _lanq('lara-admin::default.filter.all_users') }} ]
			</option>

			<option value="{!! route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['user_id' => Auth::user()->id]) !!}"
			        @if($activeUserId == Auth::user()->id) selected @endif >
				[ {{ _lanq('lara-admin::default.filter.my_content') }} ]
			</option>

			@foreach($data->authors as $authorId => $authorName)
				<option value="{!! route('admin.'.$entity->getEntityRouteKey().'.'.$entity->getMethod(), ['user_id' => $authorId]) !!}"
				        @if($activeUserId == $authorId) selected @endif >
					{{ $authorName }}
				</option>
			@endforeach

		</select>

	@endif

@endif