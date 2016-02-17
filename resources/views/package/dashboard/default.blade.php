<?php /** @var \Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection $widgetRenderers */ ?>
<?php /** @var \Dms\Core\Widget\IWidget[] $widgets */ ?>
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