<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ Admin::title() }}</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">

        <!-- Theme style -->
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/css/skins/" . config('admin.skin') .".min.css") }}">

        {!! Admin::css() !!}
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/laravel-admin/laravel-admin.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/nprogress/nprogress.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/sweetalert/dist/sweetalert.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/nestable/nestable.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/toastr/build/toastr.min.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/bootstrap3-editable/css/bootstrap-editable.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/google-fonts/fonts.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
        <link rel="stylesheet" href="{{ admin_asset("css/style.css") }}">
        <link rel="stylesheet" href="{{admin_asset("css/dataTables.bootstrap.css")}}">
        <!-- REQUIRED JS SCRIPTS -->
        <script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/js/app.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/jquery-pjax/jquery.pjax.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/nprogress/nprogress.js") }}"></script>


        <script>
function LA() {}
LA.token = "{{ csrf_token() }}";
        </script>
        <!-- REQUIRED JS SCRIPTS -->
        <script src="{{ admin_asset("/vendor/laravel-admin/nestable/jquery.nestable.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/toastr/build/toastr.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/bootstrap3-editable/js/bootstrap-editable.min.js") }}"></script>
        <script src="{{ admin_asset("/vendor/laravel-admin/sweetalert/dist/sweetalert.min.js") }}"></script>
        {!! Admin::js() !!}
        <script src="{{ admin_asset("/vendor/laravel-admin/laravel-admin/laravel-admin.js") }}"></script>
        <script src="<?php echo admin_asset("/js/admin.js"); ?>"></script>
        <script src="<?php echo admin_asset("/js/common.js"); ?>"></script>
        <script src="{{admin_asset("/js/datatables.js")}}"></script>
        <script src="{{admin_asset("/js/dataTables.bootstrap.js")}}"></script>
        <script type="text/javascript" src="{{admin_asset('js/freeze-table.js')}}"></script>

        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body class="hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">

        <input type="hidden" id="base_url" value="<?php echo admin_url(); ?>" />
        <input type="hidden" id="base_api_uri" value="<?php echo url('api/v1/')?>" />

        <div class="wrapper">
            @if (empty($is_exported_file))
                @include('admin::partials.header')
                @include('admin::partials.sidebar')
            @endif

            <div class="content-wrapper" id="pjax-container">
                @yield('content')
                {!! Admin::script() !!}
            </div>

            @include('admin::partials.footer')

        </div>

        <!-- ./wrapper -->
    </body>
</html>
