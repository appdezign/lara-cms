<?php

use Lara\Common\Models\Page;

$pages = Page::langIs($clanguage)->where('cgroup', 'page')->orderby('position', 'asc')->get();
$mpages = Page::langIs($clanguage)->where('cgroup', 'module')->orderby('position', 'asc')->get();

?>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="onpages">
		{{ _lanq('lara-admin::default.boxtitle.isglobal') }}
	</x-boxheader>

	<div id="kt_card_collapsible_onpages" class="collapse show">
		<div class="box-body">

			<x-formrow>
				<x-slot name="label">
					{{ html()->label('global:', 'isglobal') }}
				</x-slot>
				<div class="form-check">
					{{ html()->hidden('isglobal', 0) }}
					{{ html()->checkbox('isglobal', null, 1)->class('form-check-input') }}
				</div>
			</x-formrow>

		</div>
	</div>

</div>

<div class="box box-default">

	<x-boxheader cstate="active" collapseid="minus">
		{{ _lanq('lara-admin::default.boxtitle.onpages') }}
	</x-boxheader>

	<div class="box-body">

		<div class="row form-group">
			<div class="col-12 col-md-2">
				&nbsp;
			</div>
			<div class="col-12 col-md-10 col-lg-9 pb-5">

				<ul class="nested-set">

					<li class="hasChildren">
						<div class="row">

							<div class="menu-title col-1 text-right">
							</div>

							<div class="menu-title col-11 ps-0">

								<i class="fa fa-file-text-o fa-lg"></i>

								{{ ucfirst(_lanq('lara-admin::larawidget.boxtitle.pages')) }}

							</div>

						</div>

						<ul class="children">

							@foreach($pages as $page)

								<li class="isLeaf">
									<div class="row">
										<div class="menu-title col-1 text-right">
											<div class="form-check">
												@if($data->object->isglobal)
													{{ html()->checkbox('_pages_array[]', (in_array($page->id, $data->onpages)), $page->id)->class('form-check-input')->disabled() }}
												@else
													{{ html()->checkbox('_pages_array[]', (in_array($page->id, $data->onpages)), $page->id)->class('form-check-input') }}
												@endif
											</div>
										</div>

										<div class="menu-title col-11 ps-0">

											<div class="child-icon">└</div>

											@if($data->object->isglobal)
												<i class="fa fa-file-text-o fa-lg text-muted"></i>
												<span class="text-muted">{{ $page->title }}</span>
											@else
												<i class="fa fa-file-text-o fa-lg color-black"></i>
												{{ $page->title }}
											@endif

										</div>

									</div>

								</li>
							@endforeach

						</ul>

					</li>

				</ul>

				<ul class="nested-set">

					<li class="hasChildren">
						<div class="row">

							<div class="menu-title col-1 text-right">
							</div>

							<div class="menu-title col-11 ps-0">

								<i class="fa fa-file-text-o fa-lg"></i>

								{{ ucfirst(_lanq('lara-admin::larawidget.boxtitle.modulepages')) }}

							</div>

						</div>

						<ul class="children">

							@foreach($mpages as $mpage)

								<li class="isLeaf">
									<div class="row">
										<div class="menu-title col-1 text-right">
											<div class="form-check">
												@if($data->object->isglobal)
													{{ html()->checkbox('_pages_array[]', (in_array($mpage->id, $data->onpages)), $mpage->id)->class('form-check-input')->disabled() }}
												@else
													{{ html()->checkbox('_pages_array[]', (in_array($mpage->id, $data->onpages)), $mpage->id)->class('form-check-input') }}
												@endif
											</div>
										</div>

										<div class="menu-title col-11 ps-0">

											<div class="child-icon">└</div>
											@if($data->object->isglobal)
												<i class="fa fa-file-text-o fa-lg text-muted"></i>
												<span class="text-muted">{{ $mpage->title }}</span>
											@else
												<i class="fa fa-file-text-o fa-lg"></i>
												{{ $mpage->title }}
											@endif

										</div>

									</div>

								</li>
							@endforeach

						</ul>

					</li>

				</ul>
			</div>
		</div>

	</div>

</div>
