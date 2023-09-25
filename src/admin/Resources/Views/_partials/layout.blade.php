<div class="box box-default">

	<x-boxheader cstate="active" collapseid="layout">
		{{ _lanq('lara-admin::default.boxtitle.layout') }}
	</x-boxheader>

	<div id="kt_card_collapsible_layout" class="collapse show">
		<div class="box-body">

			<div class="row">
				<div class="col-md-6">

					@foreach($data->layoutoptions as $partialkey => $partial)

						<x-formrow>
							<x-slot name="label">
								{{ html()->label($partialkey, 'body') }}
							</x-slot>
							<select id="_layout_{{ $partialkey }}" name="_layout_{{ $partialkey }}"
							        class="form-select form-select-sm"
							        data-control="select2"
							        data-hide-search="true">

								@foreach($partial as $item)
									<option value="{{ $item->partialFile }}"
									        @if($item->partialFile == $data->objectlayout->$partialkey) selected @endif >
										{{ $item->friendlyName }}
										@if($item->isDefault == 'true')
											[ default ]
										@endif
									</option>
								@endforeach

							</select>
						</x-formrow>

					@endforeach

				</div>
				<div class="col-md-6">

					<div class="layout-container">
						<div class="layout-container-inner">

							<div class="layout-item-header">header &amp; menu</div>

							@if($data->objectlayout->hero != 'hidden')
								<div class="row layout-item-hero">
									<div class="col-12 layout-item-hero-inner">hero</div>
								</div>
							@endif

							@if($data->objectlayout->pagetitle != 'hidden')
								<div class="row layout-item-page-title">
									<div class="col">page title</div>
								</div>
							@endif


							@if($data->objectlayout->content == 'boxed_sidebar_right_3')
								<div class="row layout-item-main">
									<div class="col-8 layout-item-content-right">
										<div class="layout-item-content-right-inner">
											content
										</div>
									</div>
									<div class="col-4 layout-item-right">
										<div class="layout-item-right-inner">
											sidebar
										</div>
									</div>
								</div>
							@elseif($data->objectlayout->content == 'boxed_sidebar_left_3')
								<div class="row layout-item-main">
									<div class="col-4 layout-item-left">
										<div class="layout-item-left-inner">
											sidebar
										</div>
									</div>
									<div class="col-8 layout-item-content-left">
										<div class="layout-item-content-left-inner">
											content
										</div>
									</div>
								</div>
							@elseif($data->objectlayout->content == 'boxed_sidebar_leftright_3')
								<div class="row layout-item-main">
									<div class="col-3 layout-item-left">
										<div class="layout-item-left-inner">
											left
										</div>
									</div>
									<div class="col-6 layout-item-content-left-right">
										<div class="layout-item-content-left-right-inner">
											content
										</div>
									</div>
									<div class="col-3 layout-item-right">
										<div class="layout-item-right-inner">
											right
										</div>
									</div>
								</div>
							@else
								<div class="row layout-item-main">
									<div class="layout-item-content-full">content</div>
								</div>
							@endif

							@if($data->objectlayout->share != 'hidden')
								<div class="row layout-item-share">
									<div class="col">share</div>
								</div>
							@endif

							@if($data->objectlayout->cta != 'hidden')
								<div class="row layout-item-cta">
									<div class="col">cta</div>
								</div>
							@endif

							<div class="row layout-item-footer">
								<div class="col">footer</div>
							</div>

						</div>
					</div>

				</div>
			</div>

		</div>
	</div>
</div>



