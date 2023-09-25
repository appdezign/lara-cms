<!--begin::Javascript-->
<script>const hostUrl = "assets/";</script>

<script src="{{ asset('assets/admin/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/admin/metronic/scripts.bundle.js') }}"></script>

@if(config('app.env') == 'production')
<script src="{{ asset('assets/admin/js/app.min.js') }}"></script>
@else
<script src="{{ asset('assets/admin/js/app.js') }}?ver={{ date("YmdHis") }}"></script>
@endif
<!--end::Global Javascript Bundle-->


@include('lara-admin::_scripts.app')


