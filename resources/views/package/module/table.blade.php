<?php /** @var \Dms\Web\Laravel\Renderer\Table\TableRenderer $tableRenderer */ ?>
<?php /** @var string $packageName */ ?>
<?php /** @var string $moduleName */ ?>
<?php /** @var \Dms\Core\Module\ITableDisplay $table */ ?>
<?php /** @var string $viewName */ ?>
@extends('dms::template.default')

@section('body-content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    {!! $tableRenderer->renderTableControl($packageName, $moduleName, $table, $viewName) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endsection