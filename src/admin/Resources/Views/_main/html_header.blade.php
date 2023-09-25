<base href=""/>
<title>Lara {{ $laraversion->major }}.{{ $laraversion->minor }} @if(isset($entity)) | {{ ucfirst($entity->getEntityKey()) }} @endif</title>
<meta charset="utf-8" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<meta name="csrf-token" content="{{ csrf_token() }}"/>

<link rel="shortcut icon" href="{{ asset('assets/admin/img/favicon.ico') }}" />

<!--begin::Fonts(mandatory for all pages)-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:100,200,300,400,500,600,700" />
<!--end::Fonts-->

<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
<link href="{{ asset('assets/admin/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
@if(config('app.env') == 'production')
<link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" type="text/css" />
@else
<link href="{{ asset('assets/admin/css/app.css') }}?ver={{ date("YmdHis") }}" rel="stylesheet" type="text/css" />
@endif
<!--end::Global Stylesheets Bundle-->



