@extends('lara-common::layout-setup')

@section('content')

	<div class="container-fluid">
		<div class="row">
			<div class="col col-sm-10 offset-sm-1 col-md-8 offset-md-2 ">

				<!-- Content Header (Page header) -->
				<section class="content-header m-b-40">
					<h1 class="fw-light color-white text-center my-10">
						Lara 7
					</h1>
				</section>

				<!-- Main content -->
				<section class="content">

					<!-- Default box -->
					<div class="box">
						<div class="box-header with-border">
							<h3 class="box-title fw-bold color-primary">Setup - step {{ $step }}</h3>
							<span>{{ $dbname }}</span>
						</div>
						<div class="box-body px-10" style="min-height: 360px;">

							@includeIf('lara-common::setup._partials.step'.$step)

						</div>
					</div>
					<!-- /.box -->

				</section>
				<!-- /.content -->

			</div>
		</div>
	</div>

@endsection

@section('scripts-after')

	<script type="text/javascript">

		$(document).ready(function () {
			// spinner for save button
			$(".next-button").click(function () {
				$("button.next-button").html('<i class="fa fa-spin fa-circle-o-notch"></i>');
			});
		});

	</script>

@endsection
