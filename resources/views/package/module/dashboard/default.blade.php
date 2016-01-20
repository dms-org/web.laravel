<?php /** @var \Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection $widgetRenderers */ ?>
<?php /** @var \Dms\Core\Widget\IWidget[] $widgets */ ?>
<section class="content-header">
    <h1>
        Advanced Form Elements
        <small>Preview</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">{{  }}</a></li>
        <li class="active">Advanced Elements</li>
    </ol>
</section>

@foreach($widgets as $widget)
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $widget->getLabel() }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    {!! $widgetRenderers->findRendererFor($widget)->render($widget) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endforeach