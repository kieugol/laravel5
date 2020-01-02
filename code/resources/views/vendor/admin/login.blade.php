<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.5 -->
        <link rel="stylesheet" href="{{ asset("/vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset("/vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset("/vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
        <!-- iCheck -->
        <link rel="stylesheet" href="{{ asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">
        
        <link rel="stylesheet" href="{{ admin_asset("css/style.css") }}">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo">
                <img src="<?php echo asset("/images/admin-header.png") ?>" /> 
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body">
                <h3 class="login-box-msg">POS Manager</h3>

                <form action="{{ admin_base_path('auth/login') }}" method="post">
                    <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

                        @if($errors->has('username'))
                        @foreach($errors->get('username') as $message)
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label></br>
                        @endforeach
                        @endif

                        <input type="input" class="form-control" placeholder="{{ trans('admin.username') }}" name="username" value="{{ old('username') }}">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

                        @if($errors->has('password'))
                        @foreach($errors->get('password') as $message)
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label></br>
                        @endforeach
                        @endif

                        <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}" name="password">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="row">

                        <!-- /.col -->
                        <div class="col-xs-4 col-md-offset-4">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-success btn-block btn-flat">{{ trans('admin.login') }} <i class="fa fa-arrow-circle-right"></i></button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

            </div>
            <!-- /.login-box-body -->
        </div>
        <!-- /.login-box -->

        <!-- jQuery 2.1.4 -->
        <script src="{{ asset("/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
        <!-- Bootstrap 3.3.5 -->
        <script src="{{ asset("/vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
        <!-- iCheck -->
        <script src="{{ asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
        <script>
$(function () {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });
});
        </script>
    </body>
</html>
