<?php /** @var \Dms\Web\Laravel\Renderer\Module\ModuleRendererCollection $moduleRenderers */ ?>
<?php /** @var \Dms\Core\Module\IModule $module */ ?>
@extends('dms::template.default')

@section('body-content')
    {!! $moduleRenderers->findRendererFor($module)->render($module) !!}
@endsection