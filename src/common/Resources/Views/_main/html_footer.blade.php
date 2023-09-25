<!--begin::Javascript-->
<script>const hostUrl = "assets/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{ asset('assets/admin/plugins/global/plugins.bundle.js') }}"></script>
@if(config('app.env') == 'production')
<script src="{{ asset('assets/admin/js/app.min.js') }}"></script>
@else
<script src="{{ asset('assets/admin/js/app.js') }}?ver={{ date("YmdHis") }}"></script>
@endif
<!--end::Global Javascript Bundle-->

