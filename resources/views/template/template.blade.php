<!DOCTYPE html>
<?php /** @var \Dms\Core\Auth\IUser $user */ ?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @foreach (config('dms.front-end.stylesheets') as $stylesheet)
        <link rel="stylesheet" href="{{ $stylesheet }}"/>
    @endforeach
</head>
@yield('body-content')
@foreach (config('dms.front-end.scripts') as $script)
    <script src="{{ $script }}"></script>
@endforeach
</html>
