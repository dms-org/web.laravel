<?php /** @var \Dms\Web\Laravel\Renderer\Chart\ChartControlRenderer $chartRenderer */ ?>
<?php /** @var string $packageName */ ?>
<?php /** @var string $moduleName */ ?>
<?php /** @var \Dms\Core\Module\IChartDisplay $chart */ ?>
<?php /** @var string $viewName */ ?>
@extends('dms::template.default')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    {!! $chartRenderer->renderChartControl($packageName, $moduleName, $chart, $viewName) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endsection