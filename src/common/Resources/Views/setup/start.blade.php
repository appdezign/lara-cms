@extends('lara-common::layout-setup')

@section('content')

	<div class="container-fluid">
		<div class="row">
			<div class="col col-sm-10 offset-sm-1 col-md-8 offset-md-2 ">

				<!-- Content Header (Page header) -->
				<section class="content-header m-b-40">
					<h1 class="fw-light color-white text-center my-10">
						Lara {{ config('lara.lara_maj_ver') }}
					</h1>
				</section>

				<!-- Main content -->
				<section class="content">

					<!-- Default box -->
					<div class="box">
						<div class="box-header with-border">
							<h3 class="box-title fw-bold color-primary">Setup</h3>
						</div>
						<div class="box-body px-10" style="min-height: 360px;">

							@if($dbsuccess)

								{{ html()->form('POST', route('setup.start'))
									->attributes(['accept-charset' => 'UTF-8'])
									->open() }}

								<div class="row mb-5">
									<div class="col">
										{{ html()->button('start', 'submit')->id('next-button')->class('btn btn-sm btn-danger next-button float-end')->style(['width' => '100px']) }}
									</div>
								</div>

								<p>{!! $dbmessage !!}</p>


								{{ html()->form()->close() }}

							@else

								<p>{!! $dbmessage !!}</p>

							@endif
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