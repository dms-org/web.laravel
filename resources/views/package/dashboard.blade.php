<?php /** @var \Dms\Web\Laravel\Renderer\Package\PackageRendererCollection $packageRenderers */ ?>
<?php /** @var \Dms\Core\Package\IPackage $package */ ?>
@extends('dms::template.default')

@section('content')
    {!! $packageRenderers->findRendererFor($package)->render($package) !!}
@endsection