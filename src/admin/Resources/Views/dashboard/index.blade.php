@extends('lara-admin::layout')

@section('content')

	<!--begin::Toolbar-->
	<div id="kt_app_toolbar" class="app-toolbar">
		<div id="kt_app_toolbar_container" class="app-container container-fluid">
			@include($data->partials['pagetitle'])
		</div>
	</div>
	<!--end::Toolbar-->

	<!--begin::Content-->
	<div id="kt_app_content" class="app-content flex-column-fluid">
		<div id="kt_app_content_container" class="app-container container-xxl">

			<div class="content-box main-content">
				<div class="content-box-header">
					@include($data->partials['header'])
				</div>
				<div class="content-box-body">
					@include($data->partials['content'])
				</div>
			</div>

		</div>
	</div>
	<!--end::Content-->


@endsection

@section('scripts-after')



	@if(config('lara.has_cron_job') === false)
		<script>
			$(document).ready(function () {
				$.ajax({
					url: "/admin/dashboard/refresh",
					success: function (data) {
						console.log('Analytics refreshed successfully');
					}
				});
			});
		</script>
	@endif

	@if($data->site && $data->site->dates && $data->site->visitors && $data->site->pageviews)
		<script>

			var ctx1 = document.getElementById("site-stats");
			var mySiteChart = new Chart(ctx1, {
				type: 'line',
				data: {
					labels: {!! json_encode($data->site->dates->map(function($date) { return $date->format('d/m'); })) !!},
					datasets: [
						{
							label: 'visitors',
							data: {!! json_encode($data->site->visitors) !!},
							backgroundColor: 'rgba(216, 27, 96, 0.25)',
							borderColor: 'rgb(216, 27, 96)',
							fill: true,
							pointRadius: 6,
							pointHoverRadius: 8,
						},
						{
							label: 'page views',
							data: {!! json_encode($data->site->pageviews) !!},
							backgroundColor: 'rgba(42, 181, 246, 0.25)',
							borderColor: 'rgb(42, 181, 246)',
							fill: true,
							pointRadius: 6,
							pointHoverRadius: 8,
						}
					],
				},
				options: {
					responsive: true,
					aspectRatio: 4,
					title: {
						display: true,
						text: 'Analytics'
					},
					tooltips: {
						mode: 'point',
						intersect: false,
					},
					hover: {
						mode: 'point',
						intersect: true
					},
					elements: {
						line: {
							tension: 0.000001
						}
					}
				}
			});
		</script>
	@endif

	@if($data->page && $data->page->urls && $data->page->pageviews)
		<script>
			var ctx2 = document.getElementById("page-stats");
			var myPageChart = new Chart(ctx2, {
				type: 'bar',
				data: {
					labels: {!! json_encode($data->page->urls) !!},
					datasets: [
						{
							label: 'page views',
							data: {!! json_encode($data->page->pageviews) !!},
							backgroundColor: 'rgb(42, 181, 246)',
							borderColor: 'rgb(42, 181, 246)',
							fill: false,
							pointRadius: 6,
							pointHoverRadius: 8,
						}
					],
				},
				options: {
					responsive: true,
					aspectRatio: 3,
					indexAxis: 'y',
					legend: {
						display: false
					},
					title: {
						display: true,
						text: 'Top Pages'
					},
					tooltips: {
						mode: 'point',
						intersect: false,
					},
					hover: {
						mode: 'point',
						intersect: true
					},
					elements: {
						line: {
							tension: 0.000001
						}
					}
				}
			});
		</script>
	@endif

	@if($data->ref && $data->ref->urls && $data->ref->pageviews)
		<script>
			var ctx3 = document.getElementById("ref-stats");
			var myRefChart = new Chart(ctx3, {
				type: 'bar',
				data: {
					labels: {!! json_encode($data->ref->urls) !!},
					datasets: [
						{
							label: 'referrers',
							data: {!! json_encode($data->ref->pageviews) !!},
							backgroundColor: 'rgb(216, 27, 96)',
							borderColor: 'rgb(216, 27, 96)',
							fill: false,
							pointRadius: 6,
							pointHoverRadius: 8,
						}
					],
				},
				options: {
					responsive: true,
					aspectRatio: 3,
					scales: {
						y: {
							ticks: {
								stepSize: 1
							},
						},
					},
					indexAxis: 'y',
					legend: {
						display: false
					},
					title: {
						display: true,
						text: 'Top Referrers'
					},
					tooltips: {
						mode: 'point',
						intersect: false,
					},
					hover: {
						mode: 'point',
						intersect: true
					},
					elements: {
						line: {
							tension: 0.000001
						}
					}
				}
			});
		</script>
	@endif

	@if($data->user && $data->user->type && $data->user->sessions)
		<script>
			var ctx4 = document.getElementById("user-stats");
			var myUserChart = new Chart(ctx4, {
				type: 'pie',
				data: {
					labels: {!! json_encode($data->user->type) !!},
					datasets: [
						{
							label: 'users',
							data: {!! json_encode($data->user->sessions) !!},
							backgroundColor: [
								'rgb(42, 181, 246)',
								'rgb(216, 27, 96)',
							],
						}
					],
				},
				options: {
					responsive: true,
					aspectRatio: 2,
				}
			});
		</script>
	@endif

	@if($data->browser && $data->browser->type && $data->browser->sessions)
		<script>
			var ctx5 = document.getElementById("browser-stats");
			var myBrowserChart = new Chart(ctx5, {
				type: 'pie',
				data: {
					labels: {!! json_encode($data->browser->type) !!},
					datasets: [
						{
							label: 'browsers',
							data: {!! json_encode($data->browser->sessions) !!},
							backgroundColor: [
								'rgb(216, 27, 96)',
								'rgb(255, 159, 64)',
								'rgb(255, 205, 86)',
								'rgb(75, 192, 192)',
								'rgb(42, 181, 246)',
								'rgb(153, 102, 255)',
								'rgb(201, 203, 207)',
							],
						}
					]
				},
				options: {
					responsive: true,
					aspectRatio: 2,
				}
			});
		</script>
	@endif

	<script>
		var ctx6 = document.getElementById("content-stats");
		var myPageChart = new Chart(ctx6, {
			type: 'bar',
			data: {
				labels: {!! json_encode($data->content->title) !!},
				datasets: [
					{
						label: 'items',
						data: {!! json_encode($data->content->count) !!},
						backgroundColor: 'rgb(42, 181, 246)',
						borderColor: 'rgb(42, 181, 246)',
						fill: false,
						pointRadius: 6,
						pointHoverRadius: 8,
					}
				],
			},
			options: {
				responsive: true,
				aspectRatio: 2.5,
				scales: {
					y: {
						ticks: {
							stepSize: 1
						},
					},
				},
				indexAxis: 'y',
				legend: {
					display: false
				},
				title: {
					display: true,
					text: 'Content Items'
				},
				tooltips: {
					mode: 'point',
					intersect: false,
				},
				hover: {
					mode: 'point',
					intersect: true
				},
				elements: {
					line: {
						tension: 0.000001
					}
				}
			}
		});
	</script>


@endsection