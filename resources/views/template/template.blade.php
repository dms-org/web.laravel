<!DOCTYPE html>
<?php /** @var \Dms\Core\Auth\IUser $user */ ?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ asset('vendor/dms/css/all.css') }}">
</head>
@yield('body-content')
<script src="{{ asset('vendor/dms/js/all.js') }}"></script>
</html>
