<!DOCTYPE html>
<?php /** @var \Dms\Core\Auth\IUser $user */ ?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ ($pageTitle ?? false) ? $pageTitle . ' - ' : '' }}{{ $title }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @foreach (array_merge(['global'], $assetGroups ?? []) as $assetGroup)
        @foreach (config('dms.front-end.' . $assetGroup . '.stylesheets') as $stylesheet)
            <link rel="stylesheet" href="{{ asset($stylesheet) }}"/>
        @endforeach
    @endforeach
</head>
@yield('body-content')

@foreach (array_merge(['global'], $assetGroups ?? []) as $assetGroup)
    @foreach (config('dms.front-end.' . $assetGroup . '.scripts') as $script)
        <script src="{{ asset($script) }}"></script>
    @endforeach
@endforeach
@include('dms::partials.js-config')

</html>
