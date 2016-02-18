<?php /** @var \Dms\Web\Laravel\Renderer\Form\ActionFormRenderer $formRenderer */ ?>
<?php /** @var \Dms\Core\Module\IParameterizedAction $action */ ?>
@extends('dms::template.default')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    {!! $formRenderer->renderActionForm($action) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endsection