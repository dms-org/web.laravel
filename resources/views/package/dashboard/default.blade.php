<?php /** @var \Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection $widgetRenderers */ ?>
<?php /** @var \Dms\Core\Module\IModule $module */ ?>
<?php /** @var \Dms\Core\Package\IDashboardWidget[] $widgets */ ?>
@foreach($widgets as $widget)
    <?php $renderer = $widgetRenderers->findRendererFor($widget->getModule(), $widget->getWidget()) ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $widget->getWidget()->getLabel() }}</h3>
                    <div class="pull-right box-tools">
                        @foreach ($renderer->getLinks($widget->getModule(), $widget->getWidget()) as $url => $label)
                            <a class="btn btn-sm btn-default" href="{{ $url }}">
                                {{ $label }} &nbsp; <i class="fa fa-arrow-right"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    {!! $renderer->render($widget->getModule(), $widget->getWidget()) !!}
                </div>
                <!-- /.box-footer -->
            </div>
        </div>
    </div>
@endforeach